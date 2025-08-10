<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

        if (!$basePath) {
            Log::error('CopyRepositoryToHot: Missing repository directory', [
                'repository' => $this->repository,
            ]);

            return;
        }

        try {
            $this->runGitCommand('checkout master', $basePath);
            $this->runGitCommand('fetch', $basePath);
            $this->runGitCommand('reset --hard origin/master', $basePath);
            
            Log::info('CopyRepositoryToHot: Updated base repository to latest version', [
                'repository' => $this->repository,
            ]);
        } catch (ProcessFailedException $e) {
            Log::error('CopyRepositoryToHot: Failed to update base repository', [
                'repository' => $this->repository,
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);
            
            throw $e;
        }

        File::copyDirectory($basePath, $hotPath);
        
        Log::info('CopyRepositoryToHot: Successfully copied repository to hot', [
            'repository' => $this->repository,
        ]);
    }
    
    protected function runGitCommand(string $command, string $workingDirectory): Process
    {
        $fullCommand = 'git ' . $command;
        
        $process = Process::fromShellCommandline($fullCommand);
        $process->setWorkingDirectory($workingDirectory);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
