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
            
            // Run git fetch
            $fetchProcess = new Process(['git', 'fetch']);
            $fetchProcess->setWorkingDirectory(base_path());
            $fetchProcess->run();
            
            if (!$fetchProcess->isSuccessful()) {
                throw new ProcessFailedException($fetchProcess);
            }
            $output[] = "Git fetch: " . $fetchProcess->getOutput();
            
            // Run git reset --hard origin/master
            $resetProcess = new Process(['git', 'reset', '--hard', 'origin/master']);
            $resetProcess->setWorkingDirectory(base_path());
            $resetProcess->run();
            
            if (!$resetProcess->isSuccessful()) {
                throw new ProcessFailedException($resetProcess);
            }
            $output[] = "Git reset: " . $resetProcess->getOutput();
            
            // Run npm run build
            $buildProcess = new Process(['npm', 'run', 'build']);
            $buildProcess->setWorkingDirectory(base_path());
            $buildProcess->setTimeout(300); // 5 minutes timeout
            $buildProcess->run();
            
            if (!$buildProcess->isSuccessful()) {
                throw new ProcessFailedException($buildProcess);
            }
            $output[] = "NPM build: " . $buildProcess->getOutput();
            
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
}