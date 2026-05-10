<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Models\PageRepository;
use App\Services\NavigationService;

// Create instances
$pageRepository = new PageRepository(__DIR__ . '/pages');
$navigationService = new NavigationService($pageRepository);

echo "Testing Impressum...\n\n";

// Test impressum page
$page = $pageRepository->findById('impressum');

if ($page) {
    echo "✓ Impressum page found: " . $page->getTitle() . "\n";
    echo "Content type: " . $page->getContentType() . "\n";
    echo "Content length: " . strlen($page->getContent()) . " characters\n";
    echo "Content preview:\n";
    echo substr($page->getContent(), 0, 200) . "...\n";
} else {
    echo "✗ Impressum page NOT found\n";
}

// Test navigation
$footerMenu = $navigationService->generateNavigationTree('footer_menu');

echo "\nFooter menu items:\n";
foreach ($footerMenu as $item) {
    if ($item['id'] === 'impressum') {
        echo "Impressum in menu: YES\n";
        echo "  URL: " . ($item['url'] ?? 'none') . "\n";
        echo "  Page exists: " . ($item['page_exists'] ? "YES" : "NO") . "\n";
        break;
    }
}

echo "\nTest completed.\n";