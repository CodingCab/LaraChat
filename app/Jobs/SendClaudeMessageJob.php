<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class SendClaudeMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $conversation;
    protected $message;

    public function __construct(Conversation $conversation, string $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function handle(): void
    {
        $baseDirectory = storage_path('app/private/repositories/base/' . $this->conversation->repository);
        $hotDirectory = storage_path('app/private/repositories/hot/' . $this->conversation->repository);
        $projectDirectory = storage_path('app/private/repositories/projects/' . $this->conversation->project_directory);

        if (File::exists($hotDirectory)) {
            File::moveDirectory($hotDirectory, $projectDirectory);
        } else {
            File::copyDirectory($baseDirectory, $projectDirectory);
        }

        PrepareProjectDirectoryJob::dispatch($this->conversation);

        ClaudeService::stream(
            $this->message,
            '--permission-mode bypassPermissions',
            $this->conversation->claude_session_id,
            $this->conversation->filename,
            $this->conversation->project_directory
        );
    }
}
