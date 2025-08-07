#!/usr/bin/env php
<?php
$since = date('Y-m-d', strtotime('-3 months'));
$log = shell_exec("git log --since='$since' --date=short --pretty='%cd %s' --grep='#[0-9]' --all-match");
$lines = array_filter(explode("\n", trim($log)));
$weeks = [];
foreach ($lines as $line) {
    $date = substr($line, 0, 10);
    $message = trim(substr($line, 11));

    // Skip merge commits and other non-feature commits
    if (
        strpos($message, 'Merge') === 0 ||
        strpos($message, 'resolve') === 0 ||
        strpos($message, 'fix conflict') !== false
    ) {
        continue;
    }

    $week = date('o-\WW', strtotime($date));
    $weeks[$week][] = htmlspecialchars($message, ENT_QUOTES);
}
ksort($weeks);

$html = "<!DOCTYPE html>
<html lang=\"en\">
<head>
<meta charset=\"utf-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
<title>ShipTown Release Notes</title>
<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
}
.container {
    background: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
h1 {
    color: #2c3e50;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
    margin-bottom: 30px;
}
h2 {
    color: #34495e;
    background: #ecf0f1;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 4px solid #3498db;
}
ul {
    margin: 20px 0;
    padding-left: 0;
}
li {
    list-style: none;
    background: #f8f9fa;
    margin: 8px 0;
    padding: 10px 15px;
    border-radius: 4px;
    border-left: 3px solid #27ae60;
}
li:hover {
    background: #e9ecef;
}
.footer {
    text-align: center;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
    color: #6c757d;
    font-size: 14px;
}
</style>
</head>
<body>
<div class=\"container\">
<h1>ShipTown Release Notes</h1>
";

foreach (array_reverse($weeks) as $week => $messages) {
    $html .= "<h2>$week</h2>\n<ul>\n";
    foreach ($messages as $msg) {
        $html .= "  <li>$msg</li>\n";
    }
    $html .= "</ul>\n";
}

$html .= "<div class=\"footer\">
<p>Generated on " . date('Y-m-d H:i:s') . "</p>
<p><a href=\"/\">← Back to ShipTown</a></p>
</div>
</div>
</body>
</html>
";

// Ensure the directory exists
$dir = __DIR__ . '/../public/release-notes';
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}

file_put_contents($dir . '/index.html', $html);
echo "Release notes generated successfully!\n";
