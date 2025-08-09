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
            
            // Update conversation with new message and mark as processing
            $conversation->update([
                'message' => $request->input('prompt'),
                'is_processing' => true
            ]);
            
            // Dispatch job to send message to Claude
            SendClaudeMessageJob::dispatch($conversation, $request->input('prompt'));
            
            return response()->json([
                'success' => true,
                'message' => 'Message queued for processing',
                'conversationId' => $conversationId,
                'sessionFilename' => $conversation->filename,
            ]);
        }
        
        // If no conversation ID, return error (streaming without conversation is no longer supported)
        return response()->json([
            'error' => 'Conversation ID is required'
        ], 422);
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
