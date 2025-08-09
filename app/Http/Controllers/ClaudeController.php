<?php

namespace App\Http\Controllers;

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
            'conversationId' => 'required|integer|exists:conversations,id',
        ]);

        $sessionId = $request->input('sessionId');
        $conversationId = $request->input('conversationId');
        $sessionFilename = $request->input('sessionFilename');

        /** @var Conversation $conversation */
        $conversation = Conversation::findOrFail($conversationId);

        $projectDirectory = 'repositories/projects/' . $conversation->project_directory;

        // Stream the response and include conversation ID
        $response = ClaudeService::stream(
            $request->input('prompt'),
            $request->input('options', '--permission-mode bypassPermissions'),
            $sessionId,
            $conversation->filename,
            $projectDirectory,
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
