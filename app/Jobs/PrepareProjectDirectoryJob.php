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
use Illuminate\Support\Facades\Log;

class PrepareProjectDirectoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300];

    public function __construct(public Conversation $conversation)
    {
        //
    }

    public function handle(): void
    {
        $repository = $this->conversation->repository;
        $projectDirectory = $this->conversation->project_directory;

        if (!$repository || !$projectDirectory) {
            Log::error('PrepareProjectDirectoryJob: Missing repository or project_directory', [
                'conversation_id' => $this->conversation->id,
                'repository' => $repository,
                'project_directory' => $projectDirectory,
            ]);
            return;
        }

        $basePath = storage_path('app/private/repositories/base/' . $repository);
        $hotPath = storage_path('app/private/repositories/hot/' . $repository);

        if (File::exists($hotPath)) {
            return;
        }

        if (!File::exists($basePath)) {
            Log::error('PrepareProjectDirectoryJob: Base repository does not exist', [
                'repository' => $repository,
                'base_path' => $basePath,
            ]);
            throw new \Exception("Base repository {$repository} does not exist");
        }

        File::copyDirectory($basePath, $hotPath);
    }
}
