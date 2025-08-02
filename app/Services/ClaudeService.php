<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class ClaudeService
{
    public static function stream(string $prompt, string $options = '--permission-mode bypassPermissions')
    {
        return new StreamedResponse(function () use ($prompt, $options) {
            ob_implicit_flush(true);
            ob_end_flush();

            $wrapperPath = base_path('claude-wrapper.sh');
            $command = [$wrapperPath, $prompt];
            
            if ($options) {
                $optionsParts = explode(' ', $options);
                $command = array_merge($command, $optionsParts);
            }

            $process = new Process($command);
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            $process->start();

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    echo $data;
                    flush();
                } else {
                    echo "Error: " . $data;
                    flush();
                }
            }

            if (!$process->isSuccessful()) {
                echo "\nProcess exited with code: " . $process->getExitCode();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
