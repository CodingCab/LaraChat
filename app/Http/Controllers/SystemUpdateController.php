<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SystemUpdateController extends Controller
{
    public function update(Request $request)
    {
        try {
            $output = [];

            $gitFetchCommand = 'git fetch';
            $output[] = "git fetch: " . $this->runCommand($gitFetchCommand)->getOutput();

            $gitReset = 'git reset --hard origin/master';
            $output[] = "git reset: " . $this->runCommand($gitReset)->getOutput();

            $gitReset = 'composer install';
            $output[] = "composer install: " . $this->runCommand($gitReset)->getOutput();

            $gitReset = 'npm run build';
            $output[] = "npm build: " . $this->runCommand($gitReset)->getOutput();

            Log::info('System update completed successfully', ['output' => implode("\n", $output)]);

            return back()->with('flash', [
                'message' => 'System update completed successfully!',
                'output' => implode("\n\n", $output)
            ]);

        } catch (ProcessFailedException $e) {
            Log::error('System update failed', [
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);

            return back()->withErrors([
                'message' => 'System update failed: ' . $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);
        } catch (\Exception $e) {
            Log::error('System update error', ['error' => $e->getMessage()]);

            return back()->withErrors([
                'message' => 'An error occurred during the update: ' . $e->getMessage()
            ]);
        }
    }

    public function runCommand(string $command): Process
    {
        $fetchProcess = new Process(explode(' ', $command));
        $fetchProcess->setWorkingDirectory(base_path());
        $fetchProcess->run();

        if (!$fetchProcess->isSuccessful()) {
            throw new ProcessFailedException($fetchProcess);
        }

        return $fetchProcess;
    }
}
