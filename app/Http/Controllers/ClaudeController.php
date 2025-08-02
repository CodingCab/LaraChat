<?php

namespace App\Http\Controllers;

class ClaudeController extends Controller
{
    public function store($request)
    {
        // Logic to handle streaming commands to the terminal
        // This could involve using a process manager or a shell command executor
        // For example, you might use Symfony's Process component or similar

        return claude(request('command'));
    }
}
