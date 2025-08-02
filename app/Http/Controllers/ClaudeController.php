<?php

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use Exception;

class ClaudeController extends Controller
{
    /**
     * @throws Exception
     */
    public function store($request)
    {
        return ClaudeService::stream(request('command'));
    }
}
