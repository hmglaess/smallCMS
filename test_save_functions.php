<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\Admin\AdminController;
use App\Models\PageRepository;
use App\Services\NavigationService;
use App\Services\Admin\AdminService;

// Testdaten vorbereiten
$config = [
    'site' => [
        'title' => 'Test Site',
        'description' => 'Test Description'
    ],
    'theme' => [
        'primary_color' => '#3498db',
        'secondary_color' => '#2c3e50'
    ],
    'seo' => [
        'keywords' => 'test, keywords',
        'author' => 'Test Author'
    ]
];

// Services und Repository initialisieren
$pageRepository = new PageRepository(__DIR__ . '/pages');
$navigationService = new NavigationService($pageRepository, $config);
$adminService = new AdminService();
$adminController = new AdminController($pageRepository, $navigationService, $adminService, $config);

echo "Testing save functions...\n\n";

// Test 1: Settings speichern
echo "Test 1: Settings speichern\n";
$settingsData = [
    'site' => [
        'title' => 'Updated Test Site',
        'description' => 'Updated Test Description'
    ],
    'theme' => [
        'primary_color' => '#ff0000',
        'secondary_color' => '#00ff00'
    ],
    'seo' => [
        'keywords' => 'updated, test, keywords',
        'author' => 'Updated Test Author'
    ]
];

$result = $adminController->saveSettings();
// Manuell testen, da wir keine POST-Daten haben
$success = $adminService->saveSettings($settingsData);
echo "Settings saved: " . ($success ? "SUCCESS" : "FAILED") . "\n";
if ($success) {
    $savedContent = file_get_contents(__DIR__ . '/config/config.json');
    echo "Saved content: " . substr($savedContent, 0, 100) . "...\n";
}
echo "\n";

// Test 2: Menu speichern
echo "Test 2: Menu speichern\n";
$menuData = [
    ['title' => 'Home', 'page_id' => 'start', 'position' => 1],
    ['title' => 'About', 'page_id' => 'historie', 'position' => 2],
    ['title' => 'Contact', 'page_id' => 'contact', 'position' => 3]
];

$success = $adminService->saveMenuStructure($menuData, 'main_menu');
echo "Main menu saved: " . ($success ? "SUCCESS" : "FAILED") . "\n";
if ($success) {
    $savedContent = file_get_contents(__DIR__ . '/config/menu.json');
    echo "Saved content: " . substr($savedContent, 0, 100) . "...\n";
}
echo "\n";

// Test 3: Page speichern
echo "Test 3: Page speichern\n";
$existingPages = $pageRepository->findAll();
if (!empty($existingPages)) {
    // Find a page that hasn't been updated yet
    $testPage = null;
    foreach ($existingPages as $page) {
        if (strpos($page->getTitle(), '- Updated') === false) {
            $testPage = $page;
            break;
        }
    }
    
    if ($testPage) {
        $originalTitle = $testPage->getTitle();
        $originalContent = $testPage->getContent();
        
        echo "Original title: " . $originalTitle . "\n";
        echo "Original content length: " . strlen($originalContent) . "\n";
        
        // Update page
        $newTitle = $originalTitle . " - Updated";
        $newContent = $originalContent . "\n\nUpdated content at " . date('Y-m-d H:i:s');
        $testPage->setTitle($newTitle);
        $testPage->setContent($newContent);
        
        $success = $pageRepository->save($testPage);
        echo "Page saved: " . ($success ? "SUCCESS" : "FAILED") . "\n";
        
        if ($success) {
            // Verify by loading again
            $reloadedPage = $pageRepository->findById($testPage->getId());
            if ($reloadedPage) {
                echo "Reloaded title: " . $reloadedPage->getTitle() . "\n";
                echo "Reloaded content length: " . strlen($reloadedPage->getContent()) . "\n";
                
                // Check if the update was successful
                // For markdown pages, the title is extracted from the first heading
                $titleCheck = strpos($reloadedPage->getTitle(), 'Updated') !== false;
                $contentCheck = strpos($reloadedPage->getContent(), 'Updated content at') !== false;
                
                if ($titleCheck && $contentCheck) {
                    echo "✓ Page update verified successfully!\n";
                } else {
                    echo "✗ Page update verification failed! Title check: " . ($titleCheck ? "OK" : "FAIL") . ", Content check: " . ($contentCheck ? "OK" : "FAIL") . "\n";
                }
            }
        }
    } else {
        echo "All pages have already been updated, skipping page test\n";
    }
} else {
    echo "No pages found to test page saving\n";
}

echo "\nAll tests completed!";