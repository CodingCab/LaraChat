<?php

/**
 * - don not include authenticate.php file
 * - read FILE_MANAGER_KEY value from .env file without using any external packages
 * - authenticate all endpoints
 * - on received POST request, it will receive json data with array of operations, each operation/records will have "operation" key
 * - there can be only following operations: save_file, read_file, find_file, run_command
 * - in response, json which was received should be returned with addition of script results specified below
 * - script should itterate trought all array records and execute operations requested
 * - on save_file operation, record also will contain "file_name", "override"(true or false) & "content" keys, during this operation the content should be save to specified file and if the file exists, it should be only overwritten if override key is true. In response it should return "saved" key set to true (false if saving failed)
 * - on read_file operation, script should read specified file content and return "exists" key set to true or false (depending if the file exists) and "content" key of the record with content of the requested file
 * - on find_file operations, record will contain "directory" key and "search_term" that it should search for in file name for specified directory and its subfolders
 * - on run_bash operations, records will contain "script" key, its value should be saved to script.sh and script ran, the result of it should returned back in "result" key of the record
 * - include this full prompt as comment on top of the file
 */

header('Content-Type: application/json');

function getEnvValue($key)
{
    $envContent = file_get_contents(__DIR__ . '/.env');
    foreach (explode("\n", $envContent) as $line) {
        if (strpos(trim($line), "$key=") === 0) {
            return trim(explode('=', $line, 2)[1]);
        }
    }
    return null;
}

function authenticate()
{
    $headers = getallheaders();
    $fileManagerKey = getEnvValue('FILE_MANAGER_KEY');
    if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer ' . $fileManagerKey) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function handleSaveFile($record)
{
    $filePath = __DIR__ . '/' . $record['file_name'];
    try {
        if (file_exists($filePath) && !$record['override']) {
            $record['saved'] = false;
            $record['result'] = 'File already exists and override is set to false';
        } else {
            $record['saved'] = file_put_contents($filePath, $record['content']) !== false;
            $record['result'] = $record['saved'] ? 'File saved successfully' : 'Failed to save file';
        }
    } catch (Exception $e) {
        $record['saved'] = false;
        $record['result'] = 'Error saving file: ' . $e->getMessage();
    }
    return $record;
}

function handleReadFile($record)
{
    $filePath = __DIR__ . '/' . $record['file_name'];
    try {
        if (!file_exists($filePath)) {
            $record['exists'] = false;
            $record['content'] = null;
            $record['result'] = 'File does not exist';
        } else {
            $record['exists'] = true;
            $record['content'] = file_get_contents($filePath);
            $record['result'] = 'File read successfully';
        }
    } catch (Exception $e) {
        $record['exists'] = false;
        $record['content'] = null;
        $record['result'] = 'Error reading file: ' . $e->getMessage();
    }
    return $record;
}

function handleFindFile($record)
{
    $results = [];
    $directoryPath = empty($record['directory']) ? '/' : $record['directory'];
    $searchTerm = $record['search_term'] ?? '';

    try {
        if (!is_dir($directoryPath)) {
            $record['results'] = [];
            $record['result'] = 'Invalid directory specified';
            return $record;
        }

        $directory = new RecursiveDirectoryIterator($directoryPath);
        $iterator = new RecursiveIteratorIterator($directory);
        foreach ($iterator as $file) {
            if ($searchTerm === '' || strpos($file->getFilename(), $searchTerm) !== false) {
                $results[] = $file->getPathname();
            }
        }

        $record['results'] = $results;
        $record['result'] = count($results) > 0 ? 'Files found' : 'No files found';
    } catch (Exception $e) {
        $record['results'] = [];
        $record['result'] = 'Error finding files: ' . $e->getMessage();
    }
    return $record;
}

function handleRunCommand($record)
{
    try {
        file_put_contents('script.sh', $record['script']);
        $result = shell_exec('bash script.sh 2>&1');
        $record['result'] = $result ? 'Command executed successfully' : 'Command execution failed';
        $record['output'] = $result;
    } catch (Exception $e) {
        $record['result'] = 'Error executing command: ' . $e->getMessage();
        $record['output'] = null;
    }
    return $record;
}

authenticate();

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($input) || !isset($input['operations']) || !is_array($input['operations'])) {
    echo json_encode(['error' => 'Invalid input format']);
    exit;
}

foreach ($input['operations'] as &$record) {
    if (!isset($record['operation'])) {
        $record['error'] = 'Operation key missing';
        $record['result'] = 'Operation key missing';
        continue;
    }

    switch ($record['operation']) {
        case 'save_file':
            $record = handleSaveFile(array_intersect_key($record, array_flip(['file_name', 'override', 'content'])));
            break;
        case 'read_file':
            $record = handleReadFile(array_intersect_key($record, array_flip(['file_name'])));
            break;
        case 'find_file':
            $record = handleFindFile(array_intersect_key($record, array_flip(['directory', 'search_term'])));
            break;
        case 'run_command':
            $record = handleRunCommand(array_intersect_key($record, array_flip(['script'])));
            break;
        default:
            $record['error'] = 'Invalid operation';
            $record['result'] = 'Invalid operation specified';
    }
}

echo json_encode($input);
