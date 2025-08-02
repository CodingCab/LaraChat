<?php

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\Request;

class ClaudeController extends Controller
{
    /**
     * @throws Exception
     */
    public function store(Request $request)
    {
        return ClaudeService::stream(request('prompt'), request('options', '--permission-mode bypassPermissions'));
    }
}
