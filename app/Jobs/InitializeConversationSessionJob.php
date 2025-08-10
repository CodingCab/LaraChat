<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InitializeConversationSessionJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Conversation $conversation,
        protected string $message
    ) {}

    public function handle(): void
    {
        // Initialize session with the user's message (but no Claude response)
        $sessionData = [
            [
                "sessionId" => null,
                'role' => 'user',
                'userMessage' => $this->message,
                'timestamp' => now()->toIso8601String(),
                "isComplete" => false,
                "repositoryPath" => null,
            ]
        ];

        Storage::put($this->conversation->filename, json_encode($sessionData, JSON_PRETTY_PRINT));

        $from = storage_path('app/private/repositories/hot/' . $this->conversation->repository);

        if (!File::exists($from)) {
            CopyRepositoryToHotJob::dispatchSync($this->conversation->repository);
        }

        $to = storage_path($this->conversation->project_directory);

        ray($from, $to);
        
        // Ensure the parent directory exists
        $parentDir = dirname($to);
        if (!File::exists($parentDir)) {
            File::makeDirectory($parentDir, 0755, true);
        }
        
        File::moveDirectory($from, $to, true);

        // Only run git commands if the directory exists after move
        if (!File::exists($to)) {
            Log::error('InitializeConversationSessionJob: Failed to move repository directory', [
                'from' => $from,
                'to' => $to,
                'repository' => $this->conversation->repository,
            ]);
            return;
        }

        // Update the Git repository in the moved directory
        try {
            $this->runGitCommand('checkout master', $to);
            $this->runGitCommand('fetch', $to);
            $this->runGitCommand('reset --hard origin/master', $to);
            
            Log::info('InitializeConversationSessionJob: Updated project repository to latest version', [
                'repository' => $this->conversation->repository,
                'project_directory' => $this->conversation->project_directory,
            ]);
        } catch (ProcessFailedException $e) {
            Log::error('InitializeConversationSessionJob: Failed to update project repository', [
                'repository' => $this->conversation->repository,
                'project_directory' => $this->conversation->project_directory,
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);
            
            // Continue with the conversation even if Git update fails
            // The repository is still functional, just might not be on latest
        }
    }

    protected function runGitCommand(string $command, ?string $cwd = null): void
    {
        $gitCommand = "git {$command}";
        
        $result = Process::path($cwd ?? base_path())
            ->run($gitCommand);
        
        if (!$result->successful()) {
            throw new ProcessFailedException($result);
        }
    }
}