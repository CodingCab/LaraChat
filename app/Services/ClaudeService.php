<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class ClaudeService
{
    private static $runningProcesses = [];
    public static function stream(string $prompt, string $options = '--permission-mode bypassPermissions', ?string $sessionId = null, ?string $sessionFilename = null, ?string $repositoryPath = null)
    {
        // Generate a unique process ID for this request
        $processId = uniqid('claude_', true);
        
        return new StreamedResponse(function () use ($prompt, $options, $sessionId, $sessionFilename, $repositoryPath, $processId) {
            ob_implicit_flush(true);
            ob_end_flush();

            $wrapperPath = base_path('claude-wrapper.sh');
            $command = [$wrapperPath, '--print', '--verbose', '--output-format', 'stream-json'];
            
            // Use --resume for continuing an existing session with a valid UUID
            if ($sessionId && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $sessionId)) {
                $command[] = '--resume';
                $command[] = $sessionId;
            }
            
            if ($options) {
                $optionsParts = explode(' ', $options);
                $command = array_merge($command, $optionsParts);
            }
            
            $command[] = $prompt;

            // Set the working directory if repository path is provided
            $workingDirectory = null;
            if ($repositoryPath) {
                // Convert relative path to absolute path
                $workingDirectory = storage_path('app/private/' . $repositoryPath);
                
                // Check if directory exists
                if (!is_dir($workingDirectory)) {
                    \Log::error('Repository directory does not exist', [
                        'repository_path' => $repositoryPath,
                        'full_path' => $workingDirectory
                    ]);
                    throw new \Exception("Repository directory not found: {$repositoryPath}");
                }
                
                \Log::info('Setting working directory for Claude command', [
                    'repository_path' => $repositoryPath,
                    'working_directory' => $workingDirectory
                ]);
            }

            $process = new Process($command, $workingDirectory);
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            $process->start();
            
            // Store the process for potential termination
            self::$runningProcesses[$processId] = $process;
            
            // Send process ID to frontend
            echo json_encode(['type' => 'process_started', 'processId' => $processId]) . "\n";
            flush();
            
            // Initialize session data
            $rawJsonResponses = [];
            $extractedSessionId = $sessionId;
            $filename = $sessionFilename;
            
            // Generate filename if not provided
            if (!$filename) {
                $timestamp = date('Y-m-d_H-i-s');
                $filename = "{$timestamp}-claude-chat.json";
            }
            
            \Log::info('Starting Claude stream', [
                'prompt' => $prompt,
                'sessionId' => $sessionId,
                'filename' => $filename,
                'repositoryPath' => $repositoryPath
            ]);
            
            // Buffer for incomplete JSON lines
            $buffer = '';

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    // Echo the original data to the client
                    echo $data;
                    flush();
                    
                    // Process the data for saving
                    $buffer .= $data;
                    $lines = explode("\n", $buffer);
                    $buffer = array_pop($lines); // Keep incomplete line in buffer
                    
                    foreach ($lines as $line) {
                        if (trim($line)) {
                            try {
                                $jsonData = json_decode($line, true);
                                if ($jsonData) {
                                    $rawJsonResponses[] = $jsonData;
                                    
                                    \Log::info('Parsed JSON response', [
                                        'type' => $jsonData['type'] ?? 'unknown',
                                        'has_content' => isset($jsonData['content']),
                                        'response_sample' => substr(json_encode($jsonData), 0, 200)
                                    ]);
                                    
                                    // Extract session ID if not provided
                                    if (!$extractedSessionId) {
                                        $extractedSessionId = self::extractSessionId($jsonData);
                                        if ($extractedSessionId) {
                                            \Log::info('Extracted session ID', ['sessionId' => $extractedSessionId]);
                                        }
                                    }
                                    
                                    // Save after each response
                                    if ($filename) {
                                        self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, false, $repositoryPath);
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::error('JSON parsing error', [
                                    'error' => $e->getMessage(),
                                    'line' => $line
                                ]);
                            }
                        }
                    }
                } else {
                    // Send error as JSON
                    $errorJson = json_encode(['error' => $data]) . "\n";
                    echo $errorJson;
                    flush();
                    
                    $rawJsonResponses[] = ['error' => $data];
                }
            }
            
            // Process any remaining buffer
            if (trim($buffer)) {
                try {
                    $jsonData = json_decode($buffer, true);
                    if ($jsonData) {
                        $rawJsonResponses[] = $jsonData;
                    }
                } catch (\Exception $e) {
                    // Ignore JSON parsing errors
                }
            }

            // Final save with complete flag
            if ($filename) {
                self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, true, $repositoryPath);
            }

            // Clean up process from tracking
            unset(self::$runningProcesses[$processId]);
            
            // Send process ended signal
            echo json_encode(['type' => 'process_ended', 'processId' => $processId]) . "\n";
            flush();
            
            if (!$process->isSuccessful()) {
                echo json_encode(['error' => "Process exited with code: " . $process->getExitCode()]) . "\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);
    }
    
    private static function extractSessionId($jsonData): ?string
    {
        // Check for session ID in system init response
        if ($jsonData['type'] === 'system' && $jsonData['subtype'] === 'init' && isset($jsonData['session_id'])) {
            return $jsonData['session_id'];
        }
        
        // Try different possible fields for session ID
        if (isset($jsonData['sessionId'])) {
            return $jsonData['sessionId'];
        } elseif (isset($jsonData['session_id'])) {
            return $jsonData['session_id'];
        } elseif (isset($jsonData['id'])) {
            return $jsonData['id'];
        } elseif (isset($jsonData['conversationId'])) {
            return $jsonData['conversationId'];
        }
        
        return null;
    }
    
    private static function saveResponse(string $userMessage, string $filename, ?string $sessionId, ?string $extractedSessionId, array $rawJsonResponses, bool $isComplete, ?string $repositoryPath = null): void
    {
        $directory = 'claude-sessions';
        
        \Log::info('Saving response', [
            'filename' => $filename,
            'sessionId' => $sessionId,
            'response_count' => count($rawJsonResponses),
            'isComplete' => $isComplete
        ]);
        
        // Create directory if it doesn't exist
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
            \Log::info('Created claude-sessions directory');
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
                    'sessionId' => $sessionId ?? $extractedSessionId ?? \Illuminate\Support\Str::uuid()->toString(),
                    'userMessage' => $userMessage,
                    'timestamp' => now()->toIso8601String(),
                    'isComplete' => $isComplete,
                    'rawJsonResponses' => $rawJsonResponses,
                    'repositoryPath' => $repositoryPath
                ];
                
                // Check if this is a new conversation or an update to the current one
                $isNewConversation = true;
                
                // Only update if it's the last conversation and it's not complete
                if (!empty($data)) {
                    $lastIndex = count($data) - 1;
                    $lastConversation = &$data[$lastIndex];
                    
                    if (!$lastConversation['isComplete'] && 
                        $lastConversation['userMessage'] === $userMessage &&
                        $lastConversation['sessionId'] === $messageData['sessionId']) {
                        // Update the existing conversation with new responses
                        $lastConversation['rawJsonResponses'] = $rawJsonResponses;
                        $lastConversation['isComplete'] = $isComplete;
                        $lastConversation['timestamp'] = $messageData['timestamp'];
                        $isNewConversation = false;
                    }
                }
                
                // If it's a new conversation, append it
                if ($isNewConversation) {
                    $data[] = $messageData;
                }
                
                // Save the updated data
                Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        } finally {
            optional($lock)->release();
        }
    }
    
    public static function stopProcess(string $processId): bool
    {
        if (isset(self::$runningProcesses[$processId])) {
            $process = self::$runningProcesses[$processId];
            
            try {
                // Stop the process
                $process->stop(3.0); // 3 second timeout
                
                // Remove from tracking
                unset(self::$runningProcesses[$processId]);
                
                \Log::info('Claude process stopped', ['processId' => $processId]);
                
                return true;
            } catch (\Exception $e) {
                \Log::error('Error stopping Claude process', [
                    'processId' => $processId,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }
        
        return false;
    }
}
