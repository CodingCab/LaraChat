<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        dd(1);
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

        $basePath = storage_path('app/private/repositories/base');
        $hotPath = storage_path('app/private/repositories/hot');
        $projectsPath = storage_path('app/private/repositories/projects');

        $baseRepoPath = $basePath . '/' . $repository;
        $fullProjectPath = storage_path('app/private/' . $projectDirectory);

        if (!File::exists($baseRepoPath)) {
            Log::error('PrepareProjectDirectoryJob: Base repository does not exist', [
                'repository' => $repository,
                'base_path' => $baseRepoPath,
            ]);
            throw new \Exception("Base repository {$repository} does not exist");
        }

        File::ensureDirectoryExists($hotPath);
        File::ensureDirectoryExists(dirname($fullProjectPath));

        $hotRepos = File::glob($hotPath . '/' . $repository . '-*');

        if (empty($hotRepos)) {
            Log::info('PrepareProjectDirectoryJob: Hot folder is empty, copying from base', [
                'repository' => $repository,
            ]);

            $uuid = Str::uuid()->toString();
            $hotRepoPath = $hotPath . '/' . $repository . '-' . $uuid;

            $this->copyDirectory($baseRepoPath, $hotRepoPath);

            $firstFolder = $this->getFirstFolder($hotRepoPath);
            if ($firstFolder) {
                $this->moveDirectory($firstFolder, $fullProjectPath);
            } else {
                $this->moveDirectory($hotRepoPath, $fullProjectPath);
            }
        } else {
            Log::info('PrepareProjectDirectoryJob: Hot repo exists, moving to project directory', [
                'repository' => $repository,
                'hot_repo' => $hotRepos[0],
            ]);

            $hotRepoPath = $hotRepos[0];

            $firstFolder = $this->getFirstFolder($hotRepoPath);
            if ($firstFolder) {
                $this->moveDirectory($firstFolder, $fullProjectPath);

                File::deleteDirectory($hotRepoPath);
            } else {
                $this->moveDirectory($hotRepoPath, $fullProjectPath);
            }
        }

        Log::info('PrepareProjectDirectoryJob: Copying fresh hot repository for next time', [
            'repository' => $repository,
        ]);

        $uuid = Str::uuid()->toString();
        $newHotRepoPath = $hotPath . '/' . $repository . '-' . $uuid;
        $this->copyDirectory($baseRepoPath, $newHotRepoPath);

        Log::info('PrepareProjectDirectoryJob: Project directory prepared successfully', [
            'conversation_id' => $this->conversation->id,
            'project_directory' => $fullProjectPath,
        ]);
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!File::exists($source)) {
            throw new \Exception("Source directory does not exist: {$source}");
        }

        File::ensureDirectoryExists($destination);

        $command = sprintf(
            'cp -r %s %s 2>&1',
            escapeshellarg($source . '/.'),
            escapeshellarg($destination)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $error = implode("\n", $output);
            Log::error('PrepareProjectDirectoryJob: Failed to copy directory', [
                'source' => $source,
                'destination' => $destination,
                'error' => $error,
            ]);
            throw new \Exception("Failed to copy directory: {$error}");
        }
    }

    private function moveDirectory(string $source, string $destination): void
    {
        if (!File::exists($source)) {
            throw new \Exception("Source directory does not exist: {$source}");
        }

        File::ensureDirectoryExists(dirname($destination));

        if (File::exists($destination)) {
            File::deleteDirectory($destination);
        }

        $command = sprintf(
            'mv %s %s 2>&1',
            escapeshellarg($source),
            escapeshellarg($destination)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $error = implode("\n", $output);
            Log::error('PrepareProjectDirectoryJob: Failed to move directory', [
                'source' => $source,
                'destination' => $destination,
                'error' => $error,
            ]);
            throw new \Exception("Failed to move directory: {$error}");
        }
    }

    private function getFirstFolder(string $path): ?string
    {
        if (!File::exists($path) || !File::isDirectory($path)) {
            return null;
        }

        $directories = File::directories($path);

        $hiddenAndSystemDirs = ['.git', '.github', '.idea', 'node_modules', 'vendor'];

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            if (!in_array($dirName, $hiddenAndSystemDirs)) {
                return $dir;
            }
        }

        return !empty($directories) ? $directories[0] : null;
    }
}
