<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CopyRepositoryToHotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300];

    protected string $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function handle(): void
    {
        $basePath = storage_path('app/private/repositories/base/' . $this->repository);
        $hotPath = storage_path('app/private/repositories/hot/' . $this->repository);

        if ($basePath) {
            Log::error('CopyRepositoryToHot: Missing repository directory', [
                'repository' => $this->repository,
            ]);

            return;
        }

        File::copyDirectory($basePath, $hotPath);
    }
}
