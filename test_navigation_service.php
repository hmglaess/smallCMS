<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Services\NavigationService;
use App\Models\PageRepository;

// Create instances
$pageRepository = new PageRepository(__DIR__ . '/pages');
$navigationService = new NavigationService($pageRepository);

echo "Testing NavigationService...\n\n";

// Test footer menu
$footerMenu = $navigationService->generateNavigationTree('footer_menu');

echo "Footer Menu Structure:\n";
foreach ($footerMenu as $item) {
    echo "- " . $item['id'] . ": " . $item['title'] . " (exists: " . ($item['page_exists'] ? "yes" : "no") . ", is_header: " . ($item['is_header'] ? "yes" : "no") . ")\n";
}

echo "\nTest completed.\n";