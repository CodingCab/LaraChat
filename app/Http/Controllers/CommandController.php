<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class CommandController extends Controller
{
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:pwd'
        ]);

        $command = $request->input('command');
        
        try {
            $result = Process::run($command);
            
            return response()->json([
                'output' => $result->output(),
                'success' => $result->successful()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'output' => 'Error executing command: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}