<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Controllers\HomeController;
use App\Models\PageRepository;
use App\Services\NavigationService;

echo "Testing Impressum Route...\n\n";

// Simulate the HomeController behavior
$configContent = file_get_contents(__DIR__ . '/config/config.json');
$config = json_decode($configContent, true);
$pageRepository = new PageRepository(__DIR__ . '/pages');
$navigationService = new NavigationService($pageRepository);
$homeController = new HomeController($config, $pageRepository, $navigationService);

// Test the showPage method directly
try {
    ob_start();
    $homeController->showPage('impressum');
    $output = ob_get_clean();
    
    if (strpos($output, '404') !== false) {
        echo "✗ Impressum route returned 404\n";
    } else {
        echo "✓ Impressum route works\n";
        echo "Output length: " . strlen($output) . " characters\n";
        echo "Output preview:\n";
        echo substr($output, 0, 500) . "...\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";