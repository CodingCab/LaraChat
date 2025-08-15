<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DocsController extends Controller
{
    public function index()
    {
        return Inertia::render('Docs');
    }
}