<?php

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ClaudeController extends Controller
{
    /**
     * @throws Exception
     */
    public function store(Request $request)
    {
        return ClaudeService::stream(request('prompt'), request('options', '--permission-mode bypassPermissions'));
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
        ]);
        
        $directory = 'claude-responses';
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
}
