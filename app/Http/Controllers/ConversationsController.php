<?php

namespace App\Http\Controllers;

use App\Jobs\CopyRepositoryToHotJob;
use App\Jobs\InitializeConversationSessionJob;
use App\Jobs\PrepareProjectDirectoryJob;
use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class ConversationsController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->where('archived', false)
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
        
        // Get base project directory from .env
        $baseProjectDirectory = env('PROJECT_DIRECTORY', 'app/private/repositories');
        $projectDirectory = rtrim($baseProjectDirectory, '/') . '/' . $project_id;

        $conversation = Conversation::query()->create([
            'user_id' => Auth::id(),
            'title' => substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : ''),
            'message' => $msg,
            'claude_session_id' => null, // Let Claude generate this
            'project_directory' => $projectDirectory,
            'repository' => $request->input('repository'),
            'filename' => 'claude-sessions/' . date('Y-m-d\TH-i-s') . '-session-' . $project_id . '.json',
            'is_processing' => true, // Mark as processing when created
        ]);

        Bus::chain([
            new InitializeConversationSessionJob($conversation, $msg),
            new SendClaudeMessageJob($conversation, $msg)
        ])->dispatch();

        CopyRepositoryToHotJob::dispatch($conversation->repository);

        return redirect()->route('claude.conversation', $conversation->id);
    }

    public function archive(Conversation $conversation)
    {
        // Ensure the user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->archived = true;
        $conversation->save();

        return response()->json(['message' => 'Conversation archived successfully']);
    }

    public function unarchive(Conversation $conversation)
    {
        // Ensure the user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->archived = false;
        $conversation->save();

        return response()->json(['message' => 'Conversation unarchived successfully']);
    }

    public function archived()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->where('archived', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

}
