<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Models\PageRepository;

// Create instance
$pageRepository = new PageRepository(__DIR__ . '/pages');

echo "Debugging Page Loading...\n\n";

// Test loading kontakt page
$pageId = 'kontakt';
$jsonFile = __DIR__ . "/pages/{$pageId}.json";

echo "Testing page: {$pageId}\n";
echo "JSON file path: {$jsonFile}\n";
echo "File exists: " . (file_exists($jsonFile) ? "YES" : "NO") . "\n";

if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    echo "File content:\n";
    echo $content . "\n\n";
    
    $data = json_decode($content, true);
    echo "JSON decode result:\n";
    print_r($data);
    echo "\nJSON error: " . json_last_error_msg() . "\n";
    
    if (isset($data['title']) && isset($data['content'])) {
        echo "Required fields present: YES\n";
    } else {
        echo "Required fields present: NO\n";
        echo "Missing fields: ";
        if (!isset($data['title'])) echo "title ";
        if (!isset($data['content'])) echo "content ";
        echo "\n";
    }
}

echo "\nTest completed.\n";