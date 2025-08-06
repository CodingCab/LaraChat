<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StreamMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, \Illuminate\Bus\Queueable, SerializesModels;

    private string $message;
    private Conversation $conversation;

    public function __construct($conversation, $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function handle(): void
    {
        // Stream the response and include conversation ID
        $response = ClaudeService::stream(
            prompt: $this->message,
            options: '--permission-mode bypassPermissions',
            sessionId: $this->conversation->claude_session_id,
            sessionFilename: $this->conversation->filename,
            repositoryPath: $this->conversation->project_directory,
        );
    }
}
