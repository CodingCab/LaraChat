<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class ClaudeController extends Controller
{
    public function stop(Request $request)
    {
        $request->validate([
            'processId' => 'required|string',
        ]);
        
        $processId = $request->input('processId');
        $success = ClaudeService::stopProcess($processId);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Process stopped successfully' : 'Process not found or already stopped'
        ]);
    }
    
    /**
     * Create a new conversation without running Claude command
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'repository' => 'nullable|string',
        ]);
        
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // Extract title from the message (first 100 chars)
        $message = $request->input('message');
        $title = substr($message, 0, 100);
        if (strlen($message) > 100) {
            $title .= '...';
        }
        
        // Generate session ID and filename
        $sessionId = uniqid();
        $timestamp = date('Y-m-d\TH-i-s');
        $sessionFilename = $timestamp . '-sessionId-' . $sessionId . '.json';
        
        // Create empty session file with initial message structure
        $directory = 'claude-sessions';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
        
        // Initialize session with the user's message (but no Claude response)
        $sessionData = [
            [
                'role' => 'user',
                'content' => $message,
                'timestamp' => now()->toIso8601String()
            ]
        ];
        Storage::put($directory . '/' . $sessionFilename, json_encode($sessionData, JSON_PRETTY_PRINT));
        
        // Create conversation record
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => $title,
            'repository' => $request->input('repository'),
            'project_directory' => null,
            'claude_session_id' => $sessionId,
            'filename' => $sessionFilename,
        ]);
        
        // Redirect to the conversation page
        return redirect()->route('claude.conversation', $conversation->id);
    }
    
    /**
     * @throws Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'sessionId' => 'nullable|string',
            'sessionFilename' => 'nullable|string',
            'repositoryPath' => 'nullable|string',
            'conversationId' => 'nullable|integer|exists:conversations,id',
        ]);
        
        $sessionId = $request->input('sessionId');
        $conversationId = $request->input('conversationId');
        
        // Create conversation record if this is the first message (no conversationId provided)
        $sessionFilename = $request->input('sessionFilename');
        if (!$conversationId && Auth::check()) {
            // Extract title from the first user message (first 100 chars)
            $title = substr($request->input('prompt'), 0, 100);
            if (strlen($request->input('prompt')) > 100) {
                $title .= '...';
            }
            
            // Extract repository name from path if available
            $repositoryName = null;
            if ($request->input('repositoryPath')) {
                $pathParts = explode('/', $request->input('repositoryPath'));
                $repositoryName = end($pathParts);
            }
            
            // Generate filename if not provided
            if (!$sessionFilename) {
                $timestamp = date('Y-m-d\TH-i-s');
                $sessionFilename = $timestamp . '-sessionId-' . ($sessionId ?: uniqid()) . '.json';
            }
            
            // Create empty session file
            $directory = 'claude-sessions';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            Storage::put($directory . '/' . $sessionFilename, json_encode([], JSON_PRETTY_PRINT));
            
            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'title' => $title,
                'repository' => $repositoryName,
                'project_directory' => $request->input('repositoryPath'),
                'claude_session_id' => $sessionId,
                'filename' => $sessionFilename,
            ]);
            
            $conversationId = $conversation->id;
        }
        
        // Stream the response and include conversation ID
        $response = ClaudeService::stream(
            $request->input('prompt'), 
            $request->input('options', '--permission-mode bypassPermissions'),
            $sessionId,
            $sessionFilename ?: $request->input('sessionFilename'),
            $request->input('repositoryPath')
        );
        
        // Add conversation ID and filename to the response headers if created
        if ($conversationId) {
            $response->headers->set('X-Conversation-Id', (string)$conversationId);
        }
        if ($sessionFilename) {
            $response->headers->set('X-Session-Filename', $sessionFilename);
        }
        
        return $response;
    }
    
    public function saveResponse(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'message' => 'required|array',
            'message.sessionId' => 'required|string',
            'message.userMessage' => 'required|string',
            'message.timestamp' => 'required|string',
            'message.isComplete' => 'required|boolean',
            'message.rawJsonResponses' => 'required|array',
            'message.repositoryPath' => 'nullable|string',
        ]);
        
        $directory = 'claude-sessions';
        $filename = $request->input('filename');
        $message = $request->input('message');
        
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
                
                // Find if we're updating an existing conversation or adding a new one
                $updated = false;
                foreach ($data as &$conversation) {
                    // Update the last conversation if it's not complete and has the same session ID and user message
                    if (!$conversation['isComplete'] && 
                        $conversation['sessionId'] === $message['sessionId'] && 
                        $conversation['userMessage'] === $message['userMessage']) {
                        $conversation = $message;
                        $updated = true;
                        break;
                    }
                }
                
                // If not updated, add as new conversation
                if (!$updated) {
                    $data[] = $message;
                }
                
                // Save the updated data
                Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                
                return response()->json([
                    'success' => true,
                    'path' => $path,
                    'message' => 'Response saved successfully'
                ]);
            } else {
                // Could not acquire lock, return success anyway to not block frontend
                return response()->json([
                    'success' => true,
                    'path' => $path,
                    'message' => 'Response queued for saving'
                ]);
            }
        } finally {
            optional($lock)->release();
        }
    }
    
    public function getSessions()
    {
        $directory = 'claude-sessions';
        $sessions = [];
        
        if (Storage::exists($directory)) {
            $files = Storage::files($directory);
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    $filename = basename($file);
                    $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                    
                    // Remove the timestamp prefix (format: YYYY-MM-DDTHH-MM-SS-sessionId-)
                    $sessionName = $filenameWithoutExt;
                    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}-\d{2}-\d{2}-sessionId-(.+)$/', $filenameWithoutExt, $matches)) {
                        $sessionName = 'Session ' . $matches[1];
                    }
                    
                    // Read the first user message and repository from the session file
                    $userMessage = $sessionName; // Default to session name if can't read message
                    $repositoryName = null;
                    try {
                        $content = Storage::get($file);
                        $data = json_decode($content, true);
                        if (!empty($data)) {
                            if (isset($data[0]['userMessage'])) {
                                $userMessage = $data[0]['userMessage'];
                            }
                            // Get repository name from the last conversation that has one
                            foreach (array_reverse($data) as $conversation) {
                                if (!empty($conversation['repositoryPath'])) {
                                    $pathParts = explode('/', $conversation['repositoryPath']);
                                    $repositoryName = end($pathParts);
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // If we can't read the file, use the default name
                    }
                    
                    $sessions[] = [
                        'filename' => $filename,
                        'name' => $sessionName,
                        'userMessage' => $userMessage,
                        'repository' => $repositoryName,
                        'path' => $file,
                        'lastModified' => Storage::lastModified($file),
                    ];
                }
            }
            
            // Sort by last modified date, newest first
            usort($sessions, function ($a, $b) {
                return $b['lastModified'] - $a['lastModified'];
            });
        }
        
        return response()->json($sessions);
    }
    
    public function getSessionMessages($filename)
    {
        $directory = 'claude-sessions';
        $path = $directory . '/' . $filename;
        
        if (!Storage::exists($path)) {
            return response()->json(['error' => 'Session file not found'], 404);
        }
        
        $content = Storage::get($path);
        $messages = json_decode($content, true);
        
        if (!$messages) {
            return response()->json(['error' => 'Invalid session file'], 422);
        }
        
        // Log the structure for debugging
        \Log::info('Session data structure:', [
            'filename' => $filename,
            'message_count' => count($messages),
            'first_message_keys' => !empty($messages) ? array_keys($messages[0]) : [],
            'first_response_sample' => !empty($messages) && !empty($messages[0]['rawJsonResponses']) 
                ? array_slice($messages[0]['rawJsonResponses'], 0, 2) 
                : []
        ]);
        
        return response()->json($messages);
    }
    
    public function debugSession($filename)
    {
        $directory = 'claude-sessions';
        $path = $directory . '/' . $filename;
        
        if (!Storage::exists($path)) {
            return response()->json(['error' => 'Session file not found'], 404);
        }
        
        $rawContent = Storage::get($path);
        $decoded = json_decode($rawContent, true);
        
        return response()->json([
            'raw_content' => $rawContent,
            'decoded' => $decoded,
            'file_size' => Storage::size($path),
            'last_modified' => Storage::lastModified($path),
        ]);
    }
    
    public function getConversations()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();
        
        return response()->json($conversations);
    }
}
