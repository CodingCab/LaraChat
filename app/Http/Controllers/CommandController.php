<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class CommandController extends Controller
{
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');
        
        // Increase PHP execution time limit for long-running commands
        set_time_limit(600); // 10 minutes
        
        try {
            // Run the command exactly as provided
            $result = Process::timeout(300)->run($command);
            
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
}