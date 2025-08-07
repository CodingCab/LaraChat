<?php

namespace App\Abstracts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class ReportController extends Controller
{
    abstract public function index(Request $request): mixed;
}
