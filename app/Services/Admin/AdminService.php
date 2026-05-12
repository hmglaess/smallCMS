<?php

declare(strict_types=1);

namespace App\Services\Admin;

/**
 * Admin Service for administrative functions
 */
class AdminService
{
    /**
     * Get the last updated date from all pages
     *
     * @return string Formatted date string
     */
    public function getLastUpdatedDate(): string
    {
        // This will be implemented when we have access to page data
        return date('Y-m-d H:i:s');
    }

    /**
     * Scan assets directory and return file structure
     *
     * @param string $directory Path to scan
     * @return array File structure
     */
    public function scanAssetsDirectory(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                $files[$item] = [
                    'type' => 'directory',
                    'path' => $path,
                    'items' => $this->scanAssetsDirectory($path)
                ];
            } else {
                $files[$item] = [
                    'type' => 'file',
                    'path' => $path,
                    'size' => filesize($path),
                    'modified' => filemtime($path)
                ];
            }
        }

        return $files;
    }

    /**
     * Format file size for human readability
     *
     * @param int $bytes File size in bytes
     * @return string Formatted size
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Get file extension icon
     *
     * @param string $filename
     * @return string Icon representation
     */
    public function getFileIcon(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $icons = [
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'svg' => '🖼️',
            'css' => '🎨',
            'js' => '⚙️',
            'json' => '📋',
            'html' => '🌐',
            'php' => '🐘',
            'md' => '📝',
            'txt' => '📄',
        ];

        return $icons[$extension] ?? '📄';
    }

    /**
     * Generate a unique ID for new pages
     *
     * @param array $existingPages
     * @return string Unique ID
     */
    public function generateUniquePageId(array $existingPages): string
    {
        $baseId = 'new-page';
        $counter = 1;
        $newId = $baseId . '-' . $counter;

        while (isset($existingPages[$newId])) {
            $counter++;
            $newId = $baseId . '-' . $counter;
        }

        return $newId;
    }

    /**
     * Save menu structure to config file
     *
     * @param array $menuStructure
     * @param string $menuType
     * @return bool
     */
    public function saveMenuStructure(array $menuStructure, string $menuType = 'main_menu'): bool
    {
        $configFile = __DIR__ . '/../../../config/menu.json';
        
        // Load existing config
        $config = [];
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            if ($content !== false) {
                $config = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $config = [];
                }
            }
        }
    }

    /**
     * Handle image upload
     *
     * @param array $file The uploaded file from $_FILES
     * @return array Result of the upload operation
     */
    public function uploadImage(array $file): array
    {
        $uploadDir = __DIR__ . '/../../../public/assets/img/';
        $uploadFile = $uploadDir . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            $imageUrl = '/assets/img/' . basename($file['name']);
            return ['success' => true, 'url' => $imageUrl];
        } else {
            return ['success' => false, 'message' => 'Fehler beim Hochladen des Bildes.'];
        }
    }
    

    /**
     * Save settings to config file
     *
     * @param array $settings
     * @return bool
     */
    public function saveSettings(array $settings): bool
    {
        $configFile = __DIR__ . '/../../../config/config.json';
        
        $jsonContent = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($configFile, $jsonContent) !== false;
    }
}