<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Models\PageRepository;

// Create instance
$pageRepository = new PageRepository(__DIR__ . '/pages');

echo "Debugging PageRepository...\n\n";

// Check if files exist
$pagesToTest = ['kontakt', 'faq', 'anfahrt', 'impressum', 'datenschutz', 'agb'];

foreach ($pagesToTest as $pageId) {
    echo "Testing page: {$pageId}\n";
    
    $jsonFile = __DIR__ . "/pages/{$pageId}.json";
    $mdFile = __DIR__ . "/pages/{$pageId}.md";
    
    echo "  JSON file exists: " . (file_exists($jsonFile) ? "YES" : "NO") . "\n";
    echo "  MD file exists: " . (file_exists($mdFile) ? "YES" : "NO") . "\n";
    
    $page = $pageRepository->findById($pageId);
    
    if ($page) {
        echo "  ✓ Page found: " . $page->getTitle() . "\n";
    } else {
        echo "  ✗ Page NOT found by repository\n";
    }
    
    echo "\n";
}

echo "Test completed.\n";