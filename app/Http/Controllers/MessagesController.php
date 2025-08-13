<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function index(Conversation $conversation)
    {
        // Check if user owns this conversation
        if ($conversation->user_id !== auth()->id()) {
            abort(403);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'claude_session_id' => $conversation->claude_session_id,
            ]
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        // Check if user owns this conversation
        if ($conversation->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        // Update conversation with new message and mark as processing
        $conversation->update([
            'message' => $validated['content'],
            'is_processing' => true
        ]);

        // Create the user message in the database immediately
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['content'],
            'is_streaming' => false,
        ]);

        // Dispatch job to send message to Claude
        \App\Jobs\SendClaudeMessageJob::dispatch($conversation, $validated['content']);

        return response()->json([
            'success' => true,
            'message' => 'Message queued for processing',
        ]);
    }
}
