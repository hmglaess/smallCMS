<?php
/**
 * Test script for createFolder functionality
 * This script simulates the createFolder request to debug the issue
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    $container = require_once __DIR__ . '/../bootstrap/bootstrap.php';
    $controller = $container->get('App\Controllers\Admin\AdminController');
    
    // Test data
    $testData = [
        'folderName' => 'testordner_' . time(),
        'path' => ''
    ];
    
    // Simulate the request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Call the method directly
    echo "Testing createFolder method...\n\n";
    echo "Input data: " . json_encode($testData) . "\n\n";
    
    $result = $controller->createFolder();
    
    echo "Result: " . $result . "\n\n";
    
    $decoded = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Successfully decoded JSON:\n";
        echo "Success: " . ($decoded['success'] ? 'true' : 'false') . "\n";
        echo "Message: " . ($decoded['message'] ?? 'No message') . "\n";
    } else {
        echo "Failed to decode JSON: " . json_last_error_msg() . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
}