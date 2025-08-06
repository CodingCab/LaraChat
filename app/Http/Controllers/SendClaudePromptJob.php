<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SendClaudePromptJob
{

    /**
     * @throws Exception
     */
    public function store(Request $request, $sessionId, $conversationId, $sessionFilename)
    {
        // Create conversation record if this is the first message (no conversationId provided)

        if (!$conversationId && Auth::check()) {
            // Extract title from the first user message (first 100 chars)
            $title = substr($request->input('prompt'), 0, 100);
            if (strlen($request->input('prompt')) > 100) {
                $title .= '...';
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
}
