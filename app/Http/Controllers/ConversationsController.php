<?php

namespace App\Http\Controllers;

use App\Jobs\CopyRepositoryToHotJob;
use App\Jobs\PrepareProjectDirectoryJob;
use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        // Handle base64 encoded message if present
        $message = $request->input('message');
        if ($message && base64_decode($message, true) !== false) {
            $decodedMessage = base64_decode($message);
            // Check if it's actually base64 encoded text
            if (mb_check_encoding($decodedMessage, 'UTF-8')) {
                $message = $decodedMessage;
            }
        }

        // Override the request input for validation
        $request->merge(['message' => $message]);

        $request->validate([
            'message' => 'required|string',
            'repository' => 'nullable|string',
        ]);

        $project_id = uniqid();
        $msg = $request->input('message');

        $conversation = Conversation::query()->create([
            'user_id' => Auth::id(),
            'title' => substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : ''),
            'message' => $msg,
            'claude_session_id' => null, // Let Claude generate this
            'project_directory' => 'app/private/repositories/projects/' . $project_id,
            'repository' => $request->input('repository'),
            'filename' => 'claude-sessions/' . date('Y-m-d\TH-i-s') . '-session-' . $project_id . '.json',
        ]);

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

        Storage::put($conversation->filename, json_encode($sessionData, JSON_PRETTY_PRINT));

        $hotDirectory = 'app/private/repositories/hot/' . $conversation->repository;

        if (!File::exists($hotDirectory)) {
            CopyRepositoryToHotJob::dispatchSync($conversation->repository);
        }

        File::moveDirectory(storage_path($hotDirectory), storage_path($conversation->project_directory), true);

        SendClaudeMessageJob::dispatch($conversation, $msg);

        CopyRepositoryToHotJob::dispatch($conversation->repository);

        return redirect()->route('claude.conversation', $conversation->id);
    }
}
