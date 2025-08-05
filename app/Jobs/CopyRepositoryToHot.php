<?php

namespace App\Jobs;

use App\Models\Repository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CopyRepositoryToHot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300];

    protected string $hotDirectory;

    public function __construct(
        protected Repository $repository
    ) {
        $this->hotDirectory = storage_path('app/private/repositories/hot');
    }

    public function handle()
    {
        try {
            $sourcePath = storage_path('app/private/' . $this->repository->local_path);
            
            if (!File::exists($sourcePath)) {
                throw new \Exception("Repository not found at path: {$sourcePath}");
            }

            $repoName = $this->extractRepoName($this->repository->url);
            $uuid = Str::uuid()->toString();
            $repoHotDirectory = $this->hotDirectory . '/' . $repoName;
            
            if (!File::exists($repoHotDirectory)) {
                File::makeDirectory($repoHotDirectory, 0755, true);
            }

            $destinationPath = $repoHotDirectory . '/' . $repoName . '_' . $uuid;

            if (File::exists($destinationPath)) {
                File::deleteDirectory($destinationPath);
            }

            $this->copyDirectory($sourcePath, $destinationPath);

            Log::info('Repository copied to hot folder', [
                'repository' => $this->repository->name,
                'source' => $sourcePath,
                'destination' => $destinationPath,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to copy repository to hot folder', [
                'repository' => $this->repository->name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function copyDirectory(string $source, string $destination): void
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $items = File::allFiles($source, true);
        
        foreach ($items as $item) {
            $relativePath = str_replace($source . '/', '', $item->getPathname());
            $destPath = $destination . '/' . $relativePath;
            
            if ($item->isDir()) {
                if (!File::exists($destPath)) {
                    File::makeDirectory($destPath, 0755, true);
                }
            } else {
                $destDir = dirname($destPath);
                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }
                File::copy($item->getPathname(), $destPath);
            }
        }

        $directories = File::directories($source);
        foreach ($directories as $directory) {
            if (basename($directory) === '.git') {
                continue;
            }
            $destDir = $destination . '/' . basename($directory);
            $this->copyDirectory($directory, $destDir);
        }
    }

    protected function extractRepoName(string $url): string
    {
        $url = rtrim($url, '.git');
        $parts = explode('/', $url);
        return end($parts);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('CopyRepositoryToHot job failed', [
            'repository_id' => $this->repository->id,
            'repository_name' => $this->repository->name,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}