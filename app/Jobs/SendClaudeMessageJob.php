<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendClaudeMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Conversation $conversation;
    protected string $message;

    public function __construct(Conversation $conversation, string $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function handle(): void
    {
        try {
            $result = ClaudeService::processInBackground(
                $this->message,
                '--permission-mode bypassPermissions',
                $this->conversation->claude_session_id,
                $this->conversation->filename,
                $this->conversation->project_directory
            );

            // Update conversation with the session ID if extracted
            if ($result['sessionId'] && !$this->conversation->claude_session_id) {
                $this->conversation->update(['claude_session_id' => $result['sessionId']]);
            }

            // Update filename if generated
            if ($result['filename'] && !$this->conversation->filename) {
                $this->conversation->update(['filename' => $result['filename']]);
            }

            // Mark conversation as no longer processing
            $this->conversation->update(['is_processing' => false]);

            Log::info('Background Claude processing completed', [
                'conversation_id' => $this->conversation->id,
                'success' => $result['success'],
                'sessionId' => $result['sessionId']
            ]);
        } catch (\Exception $e) {
            // In case of error, mark as not processing
            $this->conversation->update(['is_processing' => false]);

            Log::error('Error in SendClaudeMessageJob', [
                'conversation_id' => $this->conversation->id,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw to let the queue handle retry logic
        }
    }
}
