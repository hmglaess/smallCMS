<?php

/**
 * Simple test script to verify admin functionality
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    // Check authentication (always true in development mode)
    $adminController = $container->get('App\Controllers\Admin\AdminController');
    
    if (!$adminController->checkAuth()) {
        http_response_code(403);
        echo 'Access denied - authentication required';
        exit;
    }
    
    // Get the action from query parameter
    $action = $_GET['action'] ?? 'dashboard';
    
    switch ($action) {
        case 'pages':
            $adminController->pages();
            break;
        case 'menu':
            $adminController->menu();
            break;
        case 'files':
            $adminController->files();
            break;
        case 'settings':
            $adminController->settings();
            break;
        case 'dashboard':
        default:
            $adminController->dashboard();
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>Error</h1>';
    echo '<p>An error occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Fatal Error</h1>';
    echo '<p>A fatal error occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}