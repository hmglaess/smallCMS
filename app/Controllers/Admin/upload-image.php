<?php

require_once __DIR__ . '/../../../app/Services/Admin/AdminService.php';

use App\Services\Admin\AdminService;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $adminService = new AdminService();
    $result = $adminService->uploadImage($_FILES['image']);
    
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
}

?>