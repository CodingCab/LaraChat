<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class ClaudeService
{
    public static function stream(string $prompt, string $options = '--permission-mode bypassPermissions', ?string $sessionId = null, ?string $sessionFilename = null)
    {
        return new StreamedResponse(function () use ($prompt, $options, $sessionId, $sessionFilename) {
            ob_implicit_flush(true);
            ob_end_flush();

            $wrapperPath = base_path('claude-wrapper.sh');
            $command = [$wrapperPath, '--print', '--verbose', '--output-format', 'stream-json'];
            
            if ($options) {
                $optionsParts = explode(' ', $options);
                $command = array_merge($command, $optionsParts);
            }
            
            $command[] = $prompt;

            $process = new Process($command);
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            $process->start();
            
            // Initialize session data
            $rawJsonResponses = [];
            $extractedSessionId = $sessionId;
            $filename = $sessionFilename;
            
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
                                    
                                    // Extract session ID if not provided
                                    if (!$extractedSessionId) {
                                        $extractedSessionId = self::extractSessionId($jsonData);
                                        if ($extractedSessionId && !$filename) {
                                            $timestamp = date('Y-m-d_H-i-s');
                                            $filename = "{$timestamp}-sessionId-{$extractedSessionId}.json";
                                        }
                                    }
                                    
                                    // Save after each response
                                    if ($filename) {
                                        self::saveResponse($prompt, $filename, $extractedSessionId, $rawJsonResponses, false);
                                    }
                                }
                            } catch (\Exception $e) {
                                // Ignore JSON parsing errors
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
                self::saveResponse($prompt, $filename, $extractedSessionId, $rawJsonResponses, true);
            }

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
        // Try different possible fields for session ID
        if (isset($jsonData['sessionId'])) {
            return $jsonData['sessionId'];
        } elseif (isset($jsonData['session_id'])) {
            return $jsonData['session_id'];
        } elseif (isset($jsonData['id'])) {
            return $jsonData['id'];
        } elseif (isset($jsonData['conversationId'])) {
            return $jsonData['conversationId'];
        } elseif ($jsonData['type'] === 'session' && isset($jsonData['session_id'])) {
            return $jsonData['session_id'];
        }
        
        return null;
    }
    
    private static function saveResponse(string $userMessage, string $filename, ?string $sessionId, array $rawJsonResponses, bool $isComplete): void
    {
        $directory = 'claude-sessions';
        
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
                    'sessionId' => $sessionId ?? 'generated-' . uniqid(),
                    'userMessage' => $userMessage,
                    'timestamp' => now()->toIso8601String(),
                    'isComplete' => $isComplete,
                    'rawJsonResponses' => $rawJsonResponses
                ];
                
                // Find if we're updating an existing conversation or adding a new one
                $updated = false;
                foreach ($data as &$conversation) {
                    // Update the last conversation if it's not complete and has the same user message
                    if (!$conversation['isComplete'] && 
                        $conversation['userMessage'] === $userMessage) {
                        $conversation = $messageData;
                        $updated = true;
                        break;
                    }
                }
                
                // If not updated, add as new conversation
                if (!$updated) {
                    $data[] = $messageData;
                }
                
                // Save the updated data
                Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        } finally {
            optional($lock)->release();
        }
    }
}
