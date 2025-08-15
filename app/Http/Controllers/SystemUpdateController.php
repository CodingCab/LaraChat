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
            // Run the refresh-master.sh script
            $scriptPath = base_path('scripts/refresh-master.sh');
            
            // Make sure the script is executable
            if (!file_exists($scriptPath)) {
                throw new \Exception('Update script not found at: ' . $scriptPath);
            }
            
            // Run the refresh-master.sh script
            $process = $this->runCommand('bash ' . escapeshellarg($scriptPath));
            $output = $process->getOutput();
            
            Log::info('System update completed successfully', ['output' => $output]);

            return back()->with('flash', [
                'message' => 'System update completed successfully!',
                'output' => $output
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
        // Set up PATH with all necessary binaries
        $herdBin = '/Users/customer/Library/Application Support/Herd/bin';
        $nodeBin = '/Users/customer/Library/Application Support/Herd/config/nvm/versions/node/v22.17.1/bin';
        $systemPath = '/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin';
        
        // Combine all paths
        $fullPath = $herdBin . ':' . $nodeBin . ':' . $systemPath;
        
        // Create the shell command with the proper PATH
        $shellCommand = 'export PATH="' . $fullPath . '" && ' . $command;
        
        // Use bash to execute the command with the environment
        $process = Process::fromShellCommandline($shellCommand);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(300); // 5 minutes timeout
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
