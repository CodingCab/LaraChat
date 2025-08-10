<?php

namespace App\Http\Controllers;

use App\Jobs\CopyRepositoryToHotJob;
use App\Jobs\PrepareProjectDirectoryJob;
use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ConversationsController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request): RedirectResponse
    {
        // Handle base64 encoded message if present
        $message = $request->input('message');
        if ($message && base64_decode($message, true) !== false) {
            $decodedMessage = base64_decode($message);
            // Check if it's actually base64 encoded text
            if (mb_check_encoding($decodedMessage, 'UTF-8')) {
                $message = $decodedMessage;
            }
        }

        // Override the request input for validation
        $request->merge(['message' => $message]);

        $request->validate([
            'message' => 'required|string',
            'repository' => 'nullable|string',
        ]);

        $project_id = uniqid();
        $msg = $request->input('message');

        $conversation = Conversation::query()->create([
            'user_id' => Auth::id(),
            'title' => substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : ''),
            'message' => $msg,
            'claude_session_id' => null, // Let Claude generate this
            'project_directory' => 'app/private/repositories/projects/' . $project_id,
            'repository' => $request->input('repository'),
            'filename' => 'claude-sessions/' . date('Y-m-d\TH-i-s') . '-session-' . $project_id . '.json',
            'is_processing' => true, // Mark as processing when created
        ]);

        // Initialize session with the user's message (but no Claude response)
        $sessionData = [
            [
                "sessionId" => null,
                'role' => 'user',
                'userMessage' => $msg,
                'timestamp' => now()->toIso8601String(),
                "isComplete" => false,
                "repositoryPath" => null,
            ]
        ];

        Storage::put($conversation->filename, json_encode($sessionData, JSON_PRETTY_PRINT));

        $from = storage_path( 'app/private/repositories/hot/' . $conversation->repository);

        if (!File::exists($from)) {
            CopyRepositoryToHotJob::dispatchSync($conversation->repository);
        }

        $to = storage_path($conversation->project_directory);

        ray($from, $to);
        File::moveDirectory($from, $to, true);

        // Update the Git repository in the moved directory
        try {
            $this->runGitCommand('checkout master', $to);
            $this->runGitCommand('fetch', $to);
            $this->runGitCommand('reset --hard origin/master', $to);
            
            Log::info('ConversationsController: Updated project repository to latest version', [
                'repository' => $conversation->repository,
                'project_directory' => $conversation->project_directory,
            ]);
        } catch (ProcessFailedException $e) {
            Log::error('ConversationsController: Failed to update project repository', [
                'repository' => $conversation->repository,
                'project_directory' => $conversation->project_directory,
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);
            
            // Continue with the conversation even if Git update fails
            // The repository is still functional, just might not be on latest
        }

        SendClaudeMessageJob::dispatch($conversation, $msg);;

        CopyRepositoryToHotJob::dispatch($conversation->repository);

        return redirect()->route('claude.conversation', $conversation->id);
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
