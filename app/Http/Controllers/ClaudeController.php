<?php

namespace App\Http\Controllers;

use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * @throws Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'sessionId' => 'nullable|string',
            'sessionFilename' => 'nullable|string',
            'conversationId' => 'nullable|integer|exists:conversations,id',
            'repositoryPath' => 'nullable|string',
        ]);

        $conversationId = $request->input('conversationId');
        
        if ($conversationId) {
            /** @var Conversation $conversation */
            $conversation = Conversation::findOrFail($conversationId);
            
            // Check if user owns this conversation
            if ($conversation->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            // Create a new conversation if none exists
            $timestamp = now()->format('Y-m-d\TH-i-s');
            $tempId = substr(uniqid(), -12);
            $filename = "claude-sessions/{$timestamp}-session-{$tempId}.json";
            
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'message' => $request->input('prompt'),
                'filename' => $filename,
                'is_processing' => true,
                'claude_session_id' => $request->input('sessionId'),
                'project_directory' => $request->input('repositoryPath'),
            ]);
            
            $conversationId = $conversation->id;
        }
        
        // Generate filename if not exists
        if (!$conversation->filename) {
            $timestamp = now()->format('Y-m-d\TH-i-s');
            $tempId = substr(uniqid(), -12);
            $filename = "claude-sessions/{$timestamp}-session-{$tempId}.json";
            $conversation->filename = $filename;
        }
        
        // Update conversation with new message and mark as processing
        $conversation->update([
            'message' => $request->input('prompt'),
            'is_processing' => true,
            'filename' => $conversation->filename
        ]);
        
        // Save user message immediately (synchronously) before queuing
        // Convert relative path to absolute if needed
        $projectDir = $conversation->project_directory;
        if ($projectDir && !str_starts_with($projectDir, '/')) {
            $projectDir = storage_path($projectDir);
        }
        
        ClaudeService::saveUserMessage(
            $request->input('prompt'),
            $conversation->filename,
            $conversation->claude_session_id,
            $projectDir
        );
        
        // Dispatch job to send message to Claude
        SendClaudeMessageJob::dispatch($conversation, $request->input('prompt'));
        
        return response()->json([
            'success' => true,
            'message' => 'Message queued for processing',
            'conversationId' => $conversationId,
            'sessionFilename' => $conversation->filename,
        ]);
    }

    public function getSessionMessages($filename)
    {
        $path = $filename;

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
}
