<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScreenshotIndexRequest;
use Illuminate\Support\Facades\File;

class ScreenshotsController extends Controller
{
    public function index(ScreenshotIndexRequest $request)
    {
        $screenshotsPath = base_path('public/img/screenshots');

        $screenshots = [];

        collect(File::allFiles($screenshotsPath))
            ->map(function ($file) use (&$screenshots) {
                $screenshots[$file->getRelativePath()][] = $file->getRelativePathname();
            });

        ksort($screenshots);

        return view('screenshots.index', compact('screenshots'));
    }
}
