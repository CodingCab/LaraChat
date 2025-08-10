<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class RepositoryController extends Controller
{
    public function index(Request $request)
    {
        $repositories = Repository::orderBy('created_at', 'desc')
            ->get();

        // Check hot folder status for each repository and ensure slug is included
        $repositories->transform(function ($repository) {
            $repoName = $this->extractRepoName($repository->url);
            $hotPattern = storage_path('app/private/repositories/hot/' . $repoName);
            $hotFolders = glob($hotPattern);
            $repository->has_hot_folder = !empty($hotFolders);
            // Ensure slug is included in the response
            $repository->makeVisible('slug');
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

        $url = $request->input('url');
        $branch = $request->input('branch');

        // Check if repository already exists
        $existingRepository = Repository::where('url', $url)
            ->first();

        if ($existingRepository) {
            return response()->json([
                'message' => 'Repository already exists',
                'repository' => $existingRepository
            ], 409);
        }

        // Extract repository name from URL
        $repoName = $this->extractRepoName($url);

        // Generate local path in base directory using repository name
        $localPath = 'repositories/base/' . $repoName;
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
        $repository = Repository::create([
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

        return response()->json([
            'message' => 'Repository copy job dispatched',
            'has_hot_folder' => false
        ]);
    }

    private function extractRepoName(string $url): string
    {
        // Remove trailing .git if present
        if (str_ends_with($url, '.git')) {
            $url = substr($url, 0, -4);
        }

        // Extract repository name from URL
        $parts = explode('/', $url);
        return end($parts);
    }
}
