<?php

require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Services\Admin\AdminService;

// Create instance
$adminService = new AdminService();

// Test data (simulating what JavaScript sends)
$testMenuData = [
    ['title' => 'Home', 'page_id' => 'start', 'position' => 10],
    ['title' => 'About', 'page_id' => 'about', 'position' => 20],
    ['title' => 'Contact', 'page_id' => 'contact', 'position' => 30]
];

echo "Testing menu save with flat structure...\n";

// Save the menu
$success = $adminService->saveMenuStructure($testMenuData, 'main_menu');

echo "Save result: " . ($success ? "SUCCESS" : "FAILED") . "\n\n";

// Read back the saved menu
$menuConfig = json_decode(file_get_contents(__DIR__ . '/config/menu.json'), true);
echo "Saved menu structure:\n";
print_r($menuConfig['main_menu'] ?? []);

echo "\nTest completed.\n";