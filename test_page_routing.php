<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Controllers\HomeController;
use App\Models\PageRepository;
use App\Services\NavigationService;

// Create instances
$configContent = file_get_contents(__DIR__ . '/config/config.json');
$config = json_decode($configContent, true);
$pageRepository = new PageRepository(__DIR__ . '/pages');
$navigationService = new NavigationService($pageRepository);
$homeController = new HomeController($config, $pageRepository, $navigationService);

echo "Testing page routing...\n\n";

// Test each footer menu page
$pagesToTest = ['kontakt', 'faq', 'anfahrt', 'impressum', 'datenschutz', 'agb'];

foreach ($pagesToTest as $pageId) {
    echo "Testing page: {$pageId}\n";
    
    // Simulate the showPage method
    $page = $pageRepository->findById($pageId);
    
    if ($page) {
        echo "  ✓ Page found: " . $page->getTitle() . "\n";
        echo "  ✓ URL would be: /page/{$pageId}\n";
    } else {
        echo "  ✗ Page NOT found\n";
    }
    
    echo "\n";
}

echo "Test completed.\n";