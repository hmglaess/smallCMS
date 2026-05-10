<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Models\PageRepository;

// Create instance
$pageRepository = new PageRepository(__DIR__ . '/pages');

echo "Testing PageRepository...\n\n";

// Get all pages
$pages = $pageRepository->findAll();

echo "Found " . count($pages) . " pages:\n";
foreach ($pages as $id => $page) {
    echo "- " . $id . ": " . $page->getTitle() . "\n";
}

echo "\nTest completed.\n";