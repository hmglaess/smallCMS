<?php

require_once __DIR__ . '/../../../app/Services/Admin/AdminService.php';

use App\Services\Admin\AdminService;

$adminService = new AdminService();
$imagesDirectory = __DIR__ . '/../../../public/assets/img';
$images = $adminService->scanAssetsDirectory($imagesDirectory);

$imageList = [];
foreach ($images as $image) {
    if ($image['type'] === 'file') {
        $imageList[] = [
            'name' => basename($image['path']),
            'url' => '/assets/img/' . basename($image['path'])
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($imageList);

?>