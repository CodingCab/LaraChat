<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SendClaudeMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $conversation;
    protected $messageContent;
    protected $message;

    public function __construct(Conversation $conversation, string $messageContent)
    {
        $this->conversation = $conversation;
        $this->messageContent = $messageContent;
    }

    public function handle(): void
    {
        // Ensure project directory exists
        if ($this->conversation->project_directory && !is_dir($this->conversation->project_directory)) {
            PrepareProjectDirectoryJob::dispatchSync($this->conversation);
        }

        // Create user message record
        $userMessage = Message::create([
            'conversation_id' => $this->conversation->id,
            'role' => 'user',
            'content' => $this->messageContent,
        ]);

        // Create assistant message placeholder
        $assistantMessage = Message::create([
            'conversation_id' => $this->conversation->id,
            'role' => 'assistant',
            'content' => '',
            'is_streaming' => true,
        ]);

        try {
            // Build the Claude command
            $wrapperPath = base_path('claude-wrapper.sh');
            $command = [$wrapperPath, '--print', '--verbose', '--output-format', 'stream-json'];

            // Use --resume for continuing an existing session
            // Only use resume if we have a valid UUID session ID from Claude
            if ($this->conversation->claude_session_id &&
                preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->conversation->claude_session_id)) {
                // Check if this is a Claude-generated session ID (not our internal ID)
                // Claude session IDs should have been extracted from a previous init response
                Log::info('Attempting to resume session', [
                    'session_id' => $this->conversation->claude_session_id,
                    'conversation_id' => $this->conversation->id
                ]);
                $command[] = '--resume';
                $command[] = $this->conversation->claude_session_id;
            } else {
                Log::info('Starting new Claude session', [
                    'existing_session_id' => $this->conversation->claude_session_id,
                    'conversation_id' => $this->conversation->id
                ]);
            }

            // Add permissions mode
            $command[] = '--permission-mode';
            $command[] = 'bypassPermissions';

            // Add the prompt
            $command[] = $this->messageContent;

            // Set working directory if repository path is provided
            $workingDirectory = null;
            if ($this->conversation->project_directory) {
                $workingDirectory = storage_path('app/private/' . $this->conversation->project_directory);

                if (!is_dir($workingDirectory)) {
                    Log::error('Repository directory does not exist', [
                        'repository_path' => $this->conversation->project_directory,
                        'full_path' => $workingDirectory
                    ]);
                    throw new \Exception("Repository directory not found: {$this->conversation->project_directory}");
                }
            }

            // Create and run the process
            $process = new Process($command, $workingDirectory);
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            Log::info('Starting Claude process', [
                'conversation_id' => $this->conversation->id,
                'command' => $command,
                'working_directory' => $workingDirectory,
            ]);

            $fullResponse = '';
            $sessionId = $this->conversation->claude_session_id;
            $rawJsonResponses = [];

            $process->run(function ($type, $buffer) use ($assistantMessage, &$fullResponse, &$sessionId, &$rawJsonResponses) {
                if (Process::OUT === $type) {
                    $lines = explode("\n", $buffer);

                    foreach ($lines as $line) {
                        if (trim($line)) {
                            try {
                                $jsonData = json_decode($line, true);
                                if ($jsonData) {
                                    // Store raw JSON response
                                    $rawJsonResponses[] = $jsonData;

                                    // Extract session ID from init response
                                    if ($jsonData['type'] === 'system' &&
                                        $jsonData['subtype'] === 'init' &&
                                        isset($jsonData['session_id'])) {
                                        $sessionId = $jsonData['session_id'];
                                        Log::info('Extracted new session ID from Claude', [
                                            'session_id' => $sessionId
                                        ]);
                                    }

                                    // Extract text content from the response
                                    if (isset($jsonData['type']) && $jsonData['type'] === 'content' && isset($jsonData['content'])) {
                                        $fullResponse .= $jsonData['content'];

                                        // Update the assistant message incrementally
                                        $assistantMessage->update([
                                            'content' => $fullResponse,
                                        ]);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Error parsing Claude response', [
                                    'error' => $e->getMessage(),
                                    'line' => $line,
                                ]);
                            }
                        }
                    }
                } else {
                    Log::error('Claude process error output', ['error' => $buffer]);
                    $rawJsonResponses[] = ['error' => $buffer];
                    
                    // Check if the error is about session not found
                    if (strpos($buffer, 'No conversation found with session ID') !== false) {
                        Log::warning('Session ID not found by Claude, will create new session', [
                            'invalid_session_id' => $this->conversation->claude_session_id,
                            'conversation_id' => $this->conversation->id
                        ]);
                        // Clear the invalid session ID so a new one will be created
                        $sessionId = null;
                    }
                }
            });

            // Update session ID if it was extracted
            if ($sessionId && $sessionId !== $this->conversation->claude_session_id) {
                $this->conversation->update([
                    'claude_session_id' => $sessionId,
                ]);
            }

            // Save raw JSON responses to session file
            if ($this->conversation->filename) {
                $this->saveToSessionFile($rawJsonResponses, $sessionId);
            }

            // Mark streaming as complete
            $assistantMessage->update([
                'is_streaming' => false,
            ]);

            Log::info('Claude message processed successfully', [
                'conversation_id' => $this->conversation->id,
                'response_length' => strlen($fullResponse),
                'json_responses_count' => count($rawJsonResponses),
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Claude message', [
                'conversation_id' => $this->conversation->id,
                'error' => $e->getMessage(),
            ]);

            // Update the assistant message with error
            $assistantMessage->update([
                'content' => 'Sorry, I encountered an error: ' . $e->getMessage(),
                'is_streaming' => false,
            ]);

            throw $e;
        }
    }

    protected function saveToSessionFile(array $rawJsonResponses, ?string $sessionId): void
    {
        $directory = 'claude-sessions';
        $filename = $this->conversation->filename;

        if (!$filename) {
            return;
        }

        // Create directory if it doesn't exist
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $path = $directory . '/' . $filename;
        $lockKey = 'file_lock_' . md5($path);

        // Use cache lock to prevent concurrent writes
        $lock = Cache::lock($lockKey, 10);

        try {
            if ($lock->get()) {
                // Read existing data or create new array
                $data = [];
                if (Storage::exists($path)) {
                    $existingContent = Storage::get($path);
                    $data = json_decode($existingContent, true) ?? [];
                }

                $messageData = [
                    'sessionId' => $sessionId ?? $this->conversation->claude_session_id ?? \Illuminate\Support\Str::uuid()->toString(),
                    'userMessage' => $this->messageContent,
                    'timestamp' => now()->toIso8601String(),
                    'isComplete' => true,
                    'rawJsonResponses' => $rawJsonResponses,
                    'repositoryPath' => $this->conversation->project_directory,
                ];

                // Check if we should update the last incomplete entry or append a new one
                $updated = false;
                if (!empty($data)) {
                    $lastIndex = count($data) - 1;
                    $lastEntry = &$data[$lastIndex];

                    // Update if the last entry is incomplete
                    // Check both old format (with 'role') and new format
                    $isIncomplete = (isset($lastEntry['isComplete']) && !$lastEntry['isComplete']) ||
                                   (isset($lastEntry['role']) && $lastEntry['role'] === 'user' && !isset($lastEntry['rawJsonResponses']));

                    $hasSameMessage = isset($lastEntry['userMessage']) &&
                                     $lastEntry['userMessage'] === $this->messageContent;

                    if ($isIncomplete && $hasSameMessage) {
                        // Update the existing entry
                        $data[$lastIndex] = $messageData;
                        $updated = true;
                        Log::info('Updated existing incomplete session entry', [
                            'index' => $lastIndex,
                            'filename' => $filename,
                            'old_entry' => $lastEntry,
                        ]);
                    }
                }

                // If we didn't update an existing entry, append a new one
                if (!$updated) {
                    $data[] = $messageData;
                    Log::info('Appended new session entry', [
                        'filename' => $filename,
                    ]);
                }

                // Save the updated data
                Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                Log::info('Saved session data to file', [
                    'filename' => $filename,
                    'path' => $path,
                    'conversation_id' => $this->conversation->id,
                    'updated_existing' => $updated,
                ]);
            }
        } finally {
            optional($lock)->release();
        }
    }
}
