<?php

namespace App\Http\Controllers;

use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $msg = $request->input('message');

        $conversation = Conversation::query()->create([
            'user_id' => Auth::id(),
            'project_directory' => uniqid(),
            'repository' => $request->input('repository'),
            'claude_session_id' => null, // Let Claude generate this
            'filename' => date('Y-m-d\TH-i-s') . '-session-' . uniqid() . '.json',
            'title' => substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : ''),
        ]);

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
                'userMessage' => $msg,
                'timestamp' => now()->toIso8601String(),
                "isComplete" => false,
                "repositoryPath" => null,
            ]
        ];

        Storage::put($directory . '/' . $conversation->filename, json_encode($sessionData, JSON_PRETTY_PRINT));

        SendClaudeMessageJob::dispatch($conversation, $msg);

        return redirect()->route('claude.conversation', $conversation->id);
    }
}
