<?php

namespace App\Http\Controllers;

use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class ConversationsController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request): RedirectResponse
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

        // Generate filename (but not session ID - Claude will generate that)
        $timestamp = date('Y-m-d\TH-i-s');
        $tempId = uniqid();
        $sessionFilename = $timestamp . '-session-' . $tempId . '.json';

        // Create empty session file with initial message structure
        $directory = 'claude-sessions';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // Initialize session with the user's message (but no Claude response)
        $sessionData = [
            [
                "sessionId" => null,
                'role' => 'user',
                'userMessage' => $message,
                'timestamp' => now()->toIso8601String(),
                "isComplete" => false,
                "repositoryPath" => null,
            ]
        ];

        Storage::put($directory . '/' . $sessionFilename, json_encode($sessionData, JSON_PRETTY_PRINT));

        // Create conversation record (without session ID - Claude will generate it)
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => $title,
            'repository' => $request->input('repository'),
            'project_directory' => uniqid(),
            'claude_session_id' => null, // Let Claude generate this
            'filename' => $sessionFilename,
        ]);

        // Dispatch job to send the message
        SendClaudeMessageJob::dispatch($conversation, $message);

        // Redirect to the conversation page
        return redirect()->route('claude.conversation', $conversation->id);
    }
}
