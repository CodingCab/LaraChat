<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use OpenAI\Laravel\Facades\OpenAI;

class ChatgptForEachFile extends Command
{
    protected $signature = 'chatgpt:for-each-file {directory : Target directory or file path} {prompt : ChatGPT prompt}';

    protected $description = 'Ask ChatGPT to modify file or files in a directory';

    public function handle(): void
    {
        $destinationFiles = $this->getFiles($this->argument('directory'));

        $totalFileCount = $destinationFiles->count();
        $destinationFiles
            ->each(function ($dirPath, $index) use ($totalFileCount) {
                $fileNumber = $index + 1;
                $this->info("Processing file $fileNumber/$totalFileCount : $dirPath");
                $this->modifyFile($dirPath, $this->argument('prompt'));
            });
    }

    private function askChatGptToModifyFile(string $content, $prompt): string
    {
        $systemContent = "Role: You are an Laravel & Vue2 developer working on a project.";

        $instructions = collect([
            'I will provide you with the content of a PHP file. Please ensure that: ',
            'The output is in pure PHP code format, with no additional comments, explanations, metadata or markdown blocks. ',
            'The code retains its original structure, indentation, and style unless modifications are requested. ',
            'Do not include any JSON or metadata about the file — only return the PHP code. ',
            'Any modifications, additions, or corrections follow the same formatting style as the input. ',
            'Please be aware that commented out lines have to be ignored, keep them as comments but not consider them as part of the code. ',
            'If setUp() method exists, place it at the bottom of the class. ',
            'Remove empty or meaningless setUp() method.',
            'Any commented-out code (lines starting with `//`, `/*`, or `#`) is ignored and should be left unchanged.',
            'Do not analyze, modify, or consider commented-out code when making changes.',
            'Focus only on active PHP code.',
            'Ensure that the code is valid PHP code and does not contain any syntax errors.',
            'There must be an empty line at the end of the PHP file.',
        ]);

        $fileContent = collect([
            'Here is the PHP file content: \n',
            $content
        ]);

        $messages = [
            [
                'role' => 'system',
                'content' => $systemContent
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ],
            [
                'role' => 'user',
                'content' => $instructions->implode("\n")
            ],
            [
                'role' => 'user',
                'content' => $fileContent->implode("\n")
            ],
        ];

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => "text"],
            'messages' => $messages,
        ]);

        return $result->choices[0]->message->content;
    }

    public function modifyFile(string $filename, $prompt): void
    {
        $sourceFileContent = File::get($filename);

        $result = $this->askChatGptToModifyFile($sourceFileContent, $prompt);

        File::put($filename, $result);
    }

    private function getFiles($path): Collection
    {
        $files = [];

        if (is_dir($path)) {
            $files = File::allFiles($path);
        } else {
            $files[] = $path;
        }

        return collect($files);
    }
}
