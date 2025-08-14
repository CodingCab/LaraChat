<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class CommandController extends Controller
{
    public function run(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->input('command');
        
        try {
            $result = Process::run($command);
            
            return response()->json([
                'output' => trim($result->output()),
                'success' => $result->successful(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'output' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}