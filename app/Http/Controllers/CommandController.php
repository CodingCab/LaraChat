<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Spatie\Ssh\Ssh;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process as SymfonyProcess;

class CommandController extends Controller
{
    protected $allowedCommands = [
        'artisan' => true,
        'php' => ['artisan'],
        'composer' => ['list', 'show', 'info', 'outdated'],
        'npm' => ['list', 'ls', 'view', 'info'],
        'ls' => true,
        'pwd' => true,
        'whoami' => true,
        'date' => true,
        'echo' => true,
    ];

    protected $blockedPatterns = [
        'rm -rf',
        'dd if=',
        'mkfs',
        'format',
        ':(){ :|:& };:',
        '/dev/null',
        'sudo',
        'su ',
        'chmod 777',
        'curl | sh',
        'wget | sh',
    ];

    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');
        
        // Check for blocked patterns
        foreach ($this->blockedPatterns as $pattern) {
            if (stripos($command, $pattern) !== false) {
                return response()->json([
                    'output' => 'This command contains blocked patterns and cannot be executed.',
                    'success' => false
                ], 403);
            }
        }
        
        // Handle Artisan commands specially
        if (str_starts_with($command, 'artisan ') || str_starts_with($command, 'php artisan ')) {
            return $this->runArtisanCommand($command);
        }
        
        // Increase PHP execution time limit for long-running commands
        set_time_limit(600); // 10 minutes
        
        // Get the projects directory path
        $projectsDir = public_path('projects');
        
        // Create projects directory if it doesn't exist
        if (!file_exists($projectsDir)) {
            mkdir($projectsDir, 0755, true);
        }
        
        try {
            // Run the command in the projects directory
            $result = Process::path($projectsDir)->timeout(300)->run($command);
            
            $output = $result->output();
            $errorOutput = $result->errorOutput();
            $fullOutput = trim($output . ($errorOutput ? "\n" . $errorOutput : ''));
            
            return response()->json([
                'output' => $fullOutput ?: 'Command completed with no output',
                'success' => $result->successful()
            ]);
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Provide more helpful error messages
            if (str_contains($errorMessage, 'timed out')) {
                $errorMessage = "Command timed out after 5 minutes. The command may still be running on the server.";
            }
            
            return response()->json([
                'output' => 'Error executing command: ' . $errorMessage,
                'success' => false
            ], 500);
        }
    }
    
    protected function runArtisanCommand($command)
    {
        // Extract artisan command from the input
        $artisanCommand = str_replace(['artisan ', 'php artisan '], '', $command);
        $parts = explode(' ', $artisanCommand);
        $commandName = array_shift($parts);
        
        try {
            $output = new BufferedOutput();
            $exitCode = Artisan::call($commandName, $parts, $output);
            
            return response()->json([
                'output' => $output->fetch(),
                'success' => $exitCode === 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'output' => 'Error: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    
    public function streamCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');
        
        // Check for blocked patterns
        foreach ($this->blockedPatterns as $pattern) {
            if (stripos($command, $pattern) !== false) {
                return response()->json([
                    'output' => 'This command contains blocked patterns and cannot be executed.',
                    'success' => false
                ], 403);
            }
        }
        
        // Get the projects directory path
        $projectsDir = public_path('projects');
        
        // Create projects directory if it doesn't exist
        if (!file_exists($projectsDir)) {
            mkdir($projectsDir, 0755, true);
        }
        
        return response()->stream(function() use ($command, $projectsDir) {
            // Disable output buffering
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set time limit for long-running commands
            set_time_limit(600);
            
            // Add claude CLI path to PATH
            $claudePath = '/Users/arturhanusek/Library/Application Support/Herd/config/nvm/versions/node/v20.19.3/bin';
            $envPath = $claudePath . ':' . getenv('PATH');
            
            // Create a process that streams output in real-time
            $process = new SymfonyProcess(
                explode(' ', $command),
                $projectsDir,
                ['PATH' => $envPath],
                null,
                300 // 5 minute timeout
            );
            
            try {
                $process->start();
                
                // Stream output as it comes
                foreach ($process as $type => $data) {
                    if ($process::OUT === $type) {
                        echo json_encode(['type' => 'stdout', 'data' => $data]) . "\n";
                    } else {
                        echo json_encode(['type' => 'stderr', 'data' => $data]) . "\n";
                    }
                    flush();
                }
                
                // Send completion event
                echo json_encode(['type' => 'complete', 'success' => $process->isSuccessful()]) . "\n";
                flush();
            } catch (\Exception $e) {
                echo json_encode(['type' => 'error', 'data' => $e->getMessage()]) . "\n";
                echo json_encode(['type' => 'complete', 'success' => false]) . "\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);
    }
    
    public function getAvailableCommands()
    {
        $artisanCommands = [];
        
        try {
            // Get all registered Artisan commands
            $commands = Artisan::all();
            foreach ($commands as $name => $command) {
                if (!str_starts_with($name, '_')) { // Skip internal commands
                    $artisanCommands[] = [
                        'name' => 'artisan ' . $name,
                        'description' => $command->getDescription()
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to get Artisan commands: ' . $e->getMessage());
        }
        
        // Add other safe commands
        $systemCommands = [
            ['name' => 'ls', 'description' => 'List directory contents'],
            ['name' => 'pwd', 'description' => 'Print working directory'],
            ['name' => 'whoami', 'description' => 'Display current user'],
            ['name' => 'date', 'description' => 'Display current date and time'],
            ['name' => 'composer list', 'description' => 'List Composer commands'],
            ['name' => 'npm list', 'description' => 'List installed npm packages'],
        ];
        
        return response()->json([
            'artisan' => $artisanCommands,
            'system' => $systemCommands
        ]);
    }
    
    public function getCommandHistory(Request $request)
    {
        // For now, return empty array. In a real implementation,
        // you'd store and retrieve from database or session
        return response()->json([]);
    }
}