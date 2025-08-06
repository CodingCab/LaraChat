#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Conversation;
use App\Jobs\SendClaudeMessageJob;

// Get the most recent conversation or create a test one
$conversation = Conversation::latest()->first();

if (!$conversation) {
    echo "No conversations found. Please create one through the UI first.\n";
    exit(1);
}

echo "Testing with conversation ID: {$conversation->id}\n";
echo "Title: {$conversation->title}\n";
echo "Filename: {$conversation->filename}\n";

// Dispatch the job synchronously for testing
try {
    SendClaudeMessageJob::dispatchSync($conversation, "Test message from script");
    echo "Job executed successfully!\n";
} catch (\Exception $e) {
    echo "Job failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}