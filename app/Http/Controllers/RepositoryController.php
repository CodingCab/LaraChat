<?php

namespace App\Http\Controllers;

use App\Jobs\CopyRepositoryToHot;
use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RepositoryController extends Controller
{
    public function index(Request $request)
    {
        $repositories = $request->user()->repositories()
            ->orderBy('created_at', 'desc')
            ->get();

        // Check hot folder status for each repository
        $repositories->transform(function ($repository) {
            $repoName = $this->extractRepoName($repository->url);
            $hotPattern = storage_path('app/private/repositories/hot/' . $repoName . '/' . $repoName . '_*');
            $hotFolders = glob($hotPattern);
            $repository->has_hot_folder = !empty($hotFolders);
            return $repository;
        });

        return $repositories;
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'branch' => 'nullable|string',
        ]);

        $user = $request->user();
        $url = $request->input('url');
        $branch = $request->input('branch');

        // Check if repository already exists for this user
        $existingRepository = $user->repositories()
            ->where('url', $url)
            ->first();

        if ($existingRepository) {
            return response()->json([
                'message' => 'Repository already exists',
                'repository' => $existingRepository
            ], 409);
        }

        // Extract repository name from URL
        $repoName = $this->extractRepoName($url);

        // Generate unique local path in base directory
        $localPath = 'repositories/base/' . Str::slug($repoName) . '-' . Str::random(6);
        $fullPath = storage_path('app/private/' . $localPath);

        // Create directory if it doesn't exist
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        // Clone the repository
        if ($branch) {
            // If branch is specified, try to clone with that branch
            $result = Process::run("git clone --depth=1 --branch {$branch} {$url} {$fullPath}");

            if (!$result->successful() && str_contains($result->errorOutput(), 'Remote branch')) {
                // If the branch doesn't exist, try cloning without specifying branch
                $result = Process::run("git clone {$url} {$fullPath} --depth=1");
            }
        } else {
            // No branch specified, clone with default branch
            $result = Process::run("git clone {$url} {$fullPath} --depth=1");
        }

        if (!$result->successful()) {
            // Clean up if directory was created
            if (file_exists($fullPath)) {
                Process::run("rm -rf {$fullPath}");
            }

            return response()->json([
                'message' => 'Failed to clone repository',
                'error' => $result->errorOutput()
            ], 422);
        }

        // Get the actual branch that was cloned
        $branchResult = Process::path($fullPath)->run('git branch --show-current');
        $actualBranch = trim($branchResult->output());

        // Create repository record
        $repository = $user->repositories()->create([
            'name' => $repoName,
            'url' => $url,
            'local_path' => $localPath,
            'branch' => $actualBranch ?: 'master',
            'last_pulled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Repository cloned successfully',
            'repository' => $repository
        ]);
    }

    public function destroy(Repository $repository)
    {
        // Ensure user owns the repository
        if ($repository->user_id !== auth()->id()) {
            abort(403);
        }

        // Delete local repository
        $fullPath = storage_path('app/private/' . $repository->local_path);
        if (file_exists($fullPath)) {
            Process::run("rm -rf {$fullPath}");
        }

        // Delete database record
        $repository->delete();

        return response()->json([
            'message' => 'Repository deleted successfully'
        ]);
    }

    public function pull(Repository $repository)
    {
        // Ensure user owns the repository
        if ($repository->user_id !== auth()->id()) {
            abort(403);
        }

        $fullPath = storage_path('app/private/' . $repository->local_path);

        // Pull latest changes
        $result = Process::path($fullPath)->run('git pull');

        if (!$result->successful()) {
            return response()->json([
                'message' => 'Failed to pull repository',
                'error' => $result->errorOutput()
            ], 422);
        }

        // Update last pulled timestamp
        $repository->update(['last_pulled_at' => now()]);

        return response()->json([
            'message' => 'Repository updated successfully',
            'repository' => $repository
        ]);
    }

    public function copyToHot(Repository $repository)
    {
        // Ensure user owns the repository
        if ($repository->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if hot folder already exists
        $repoName = $this->extractRepoName($repository->url);
        $hotPattern = storage_path('app/private/repositories/hot/' . $repoName . '/' . $repoName . '_*');
        $hotFolders = glob($hotPattern);
        if (!empty($hotFolders)) {
            return response()->json([
                'message' => 'Hot folder already exists',
                'has_hot_folder' => true
            ]);
        }

        // Dispatch job to copy repository to hot folder
        CopyRepositoryToHot::dispatchAfterResponse($repository);

        return response()->json([
            'message' => 'Repository copy job dispatched',
            'has_hot_folder' => false
        ]);
    }

    private function extractRepoName(string $url): string
    {
        // Remove trailing .git if present
        $url = rtrim($url, '.git');

        // Extract repository name from URL
        $parts = explode('/', $url);
        return end($parts);
    }
}
