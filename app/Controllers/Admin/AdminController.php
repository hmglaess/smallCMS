<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Page;
use App\Models\PageRepository;
use App\Services\NavigationService;
use App\Services\Admin\AdminService;
use App\Services\SidebarService;

/**
 * Admin Controller for managing the administration interface
 */
class AdminController
{
    private PageRepository $pageRepository;
    private NavigationService $navigationService;
    private AdminService $adminService;
    private array $config;
    private SidebarService $sidebarService;

    /**
     * AdminController constructor
     *
     * @param PageRepository $pageRepository
     * @param NavigationService $navigationService
     * @param AdminService $adminService
     * @param array $config
     * @param SidebarService $sidebarService
     */
    public function __construct(
        PageRepository $pageRepository,
        NavigationService $navigationService,
        AdminService $adminService,
        array $config,
        SidebarService $sidebarService
    ) {
        $this->pageRepository = $pageRepository;
        $this->navigationService = $navigationService;
        $this->adminService = $adminService;
        $this->config = $config;
        $this->sidebarService = $sidebarService;
    }

    /**
     * Admin Dashboard - Main entry point
     */
    public function dashboard()
    {
        // Get basic statistics for dashboard
        $stats = [
            'totalPages' => count($this->pageRepository->findAll()),
            'menuItems' => count($this->navigationService->getFlatNavigation('main_menu')),
            'lastUpdated' => $this->adminService->getLastUpdatedDate(),
        ];

        // Render the complete admin layout
        echo $this->renderAdminLayout(
            $this->renderDashboardContent($stats),
            'Dashboard'
        );
    }
    
    /**
     * Render dashboard content
     *
     * @param array $stats
     * @return string
     */
    private function renderDashboardContent(array $stats): string
    {
        ob_start();
        include __DIR__ . '/../../Views/admin/dashboard.php';
        return ob_get_clean();
    }

    /**
     * Page Management Interface
     */
    public function pages()
    {
        $pages = $this->pageRepository->findAll();
        
        echo $this->renderAdminLayout(
            $this->renderPagesContent($pages),
            'Seitenverwaltung'
        );
    }
    
    /**
     * Render pages content
     *
     * @param array $pages
     * @return string
     */
    private function renderPagesContent(array $pages): string
    {
        ob_start();
        include __DIR__ . '/../../Views/admin/pages.php';
        return ob_get_clean();
    }

    /**
     * Menu Management Interface
     */
    public function menu()
    {
        $menuStructure = $this->navigationService->generateNavigationTree('main_menu');
        $footerMenuStructure = $this->navigationService->generateNavigationTree('footer_menu');
        
        // Get all available pages for the dropdown
        $allPages = $this->pageRepository->findAll();
        $availablePageIds = array_keys($allPages);

        echo $this->renderAdminLayout(
            $this->renderMenuContent($menuStructure, $footerMenuStructure, $availablePageIds),
            'Menüverwaltung'
        );
    }
    
    /**
     * Render menu content
     *
     * @param array $menuStructure
     * @param array $footerMenuStructure
     * @param array $availablePageIds
     * @return string
     */
    private function renderMenuContent(array $menuStructure, array $footerMenuStructure, array $availablePageIds = []): string
    {
        ob_start();
        include __DIR__ . '/../../Views/admin/menu.php';
        return ob_get_clean();
    }

    /**
     * File Manager Interface
     */
    public function files()
    {
        $assetsDir = __DIR__ . '/../../../public/assets';
        $subDirectory = $_GET['directory'] ?? '';
        
        if (!empty($subDirectory)) {
            $assetsDir .= '/' . $subDirectory;
        }
        
        $files = $this->adminService->scanAssetsDirectory($assetsDir);

        // Wenn es sich um eine AJAX-Anfrage handelt, geben wir nur den Inhalt zurück
        $isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjaxRequest) {
            // Nur den file-manager-Inhalt zurückgeben
            echo $this->renderFilesContent($files);
        } else {
            // Komplette Seite mit Layout zurückgeben
            echo $this->renderAdminLayout(
                $this->renderFilesContent($files),
                'Dateiverwaltung'
            );
        }
    }

    /**
     * Handle file upload
     *
     * @return string JSON response
     */
    public function uploadFile(): string
    {
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return json_encode(['success' => false, 'message' => 'Keine Datei hochgeladen']);
        }
        
        $directory = $_POST['directory'] ?? '';
        $assetsDir = __DIR__ . '/../../../public/assets';
        
        if (!empty($directory)) {
            $assetsDir .= '/' . $directory;
            // Create directory if it doesn't exist
            if (!is_dir($assetsDir)) {
                mkdir($assetsDir, 0755, true);
            }
        }
        
        $file = $_FILES['file'];
        $targetPath = $assetsDir . '/' . basename($file['name']);
        
        // Move uploaded file to target directory
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return json_encode(['success' => true, 'message' => 'Datei erfolgreich hochgeladen']);
        } else {
            return json_encode(['success' => false, 'message' => 'Fehler beim Verschieben der Datei']);
        }
    }

    /**
     * Create a new folder
     *
     * @return string JSON response
     */
    public function createFolder(): string
    {
        // Log the request for debugging
        error_log('createFolder method called');
        
        try {
            $input = file_get_contents('php://input');
            error_log('Input received: ' . ($input ?: 'empty'));
            
            if ($input === false) {
                error_log('No input data received');
                return json_encode(['success' => false, 'message' => 'Keine Eingabedaten erhalten']);
            }
            
            $data = json_decode($input, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log('Invalid JSON: ' . json_last_error_msg());
                return json_encode(['success' => false, 'message' => 'Ungültige JSON-Daten: ' . json_last_error_msg()]);
            }
            
            error_log('Decoded data: ' . print_r($data, true));
            
            $data = $data ?? [];
            
            if (empty($data['folderName'])) {
                error_log('No folder name provided');
                return json_encode(['success' => false, 'message' => 'Kein Ordnernamen angegeben']);
            }
            
            $folderName = $data['folderName'];
            $path = $data['path'] ?? '';
            
            error_log('Creating folder: ' . $folderName . ' in path: ' . $path);
            
            // Validate folder name
            if (preg_match('/[^a-zA-Z0-9_\-\.]/', $folderName)) {
                error_log('Invalid characters in folder name');
                return json_encode(['success' => false, 'message' => 'Ungültige Zeichen im Ordnernamen']);
            }
            
            $assetsDir = __DIR__ . '/../../../public/assets';
            
            if (!empty($path)) {
                $assetsDir .= '/' . $path;
                // Ensure parent directory exists
                if (!is_dir($assetsDir)) {
                    error_log('Creating parent directory: ' . $assetsDir);
                    if (!mkdir($assetsDir, 0755, true)) {
                        error_log('Failed to create parent directory');
                        return json_encode(['success' => false, 'message' => 'Konnte übergeordnetes Verzeichnis nicht erstellen']);
                    }
                }
            }
            
            $newFolderPath = $assetsDir . '/' . $folderName;
            
            // Check if folder already exists
            if (is_dir($newFolderPath)) {
                error_log('Folder already exists: ' . $newFolderPath);
                return json_encode(['success' => false, 'message' => 'Ordner existiert bereits']);
            }
            
            // Create the folder
            error_log('Attempting to create folder at: ' . $newFolderPath);
            if (mkdir($newFolderPath, 0755, true)) {
                error_log('Folder created successfully');
                return json_encode(['success' => true, 'message' => 'Ordner erfolgreich erstellt']);
            } else {
                $error = error_get_last();
                error_log('Failed to create folder: ' . ($error['message'] ?? 'Unknown error'));
                return json_encode(['success' => false, 'message' => 'Fehler beim Erstellen des Ordners: ' . ($error['message'] ?? 'Unbekannter Fehler')]);
            }
        } catch (\Exception $e) {
            error_log('Exception: ' . $e->getMessage());
            return json_encode(['success' => false, 'message' => 'Ausnahme beim Erstellen des Ordners: ' . $e->getMessage()]);
        } catch (\Throwable $t) {
            error_log('Throwable: ' . $t->getMessage());
            return json_encode(['success' => false, 'message' => 'Fehler beim Erstellen des Ordners: ' . $t->getMessage()]);
        }
    }

    /**
     * Delete a file or folder
     *
     * @return string JSON response
     */
    public function deleteFile(): string
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $name = $data['name'] ?? '';
            $path = $data['path'] ?? '';
            $isFolder = $data['isFolder'] ?? false;
            
            if (empty($name)) {
                return json_encode(['success' => false, 'message' => 'Kein Name angegeben']);
            }
            
            // Protected folders that cannot be deleted
            $protectedFolders = ['css', 'img', 'js'];
            if ($isFolder && in_array(strtolower($name), $protectedFolders)) {
                return json_encode(['success' => false, 'message' => 'Der Ordner "' . $name . '" ist geschützt und kann nicht gelöscht werden.']);
            }
            
            $assetsDir = __DIR__ . '/../../../public/assets';
            
            if (!empty($path)) {
                $assetsDir .= '/' . $path;
            }
            
            $targetPath = $assetsDir . '/' . $name;
            
            // Check if target exists
            if (!file_exists($targetPath)) {
                return json_encode(['success' => false, 'message' => 'Datei/Ordner nicht gefunden']);
            }
            
            // Delete the file or folder
            if ($isFolder) {
                // Use recursive directory deletion
                $this->deleteDirectory($targetPath);
            } else {
                // Delete single file
                if (!unlink($targetPath)) {
                    return json_encode(['success' => false, 'message' => 'Fehler beim Löschen der Datei']);
                }
            }
            
            return json_encode(['success' => true, 'message' => 'Erfolgreich gelöscht']);
            
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()]);
        } catch (\Throwable $t) {
            return json_encode(['success' => false, 'message' => 'Fehler beim Löschen: ' . $t->getMessage()]);
        }
    }

    /**
     * Recursively delete a directory
     *
     * @param string $dirPath Path to directory to delete
     * @return bool True on success, false on failure
     */
    private function deleteDirectory(string $dirPath): bool
    {
        if (!is_dir($dirPath)) {
            return false;
        }
        
        // Open the directory
        $files = array_diff(scandir($dirPath), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dirPath . '/' . $file;
            
            if (is_dir($path)) {
                // Recursively delete subdirectories
                $this->deleteDirectory($path);
            } else {
                // Delete files
                unlink($path);
            }
        }
        
        // Delete the now-empty directory
        return rmdir($dirPath);
    }
    
    /**
     * Render files content
     *
     * @param array $files
     * @return string
     */
    private function renderFilesContent(array $files): string
    {
        ob_start();
        include __DIR__ . '/../../Views/admin/files.php';
        return ob_get_clean();
    }

    /**
     * Settings Interface
     */
    public function settings()
    {
        $settings = $this->config;

        echo $this->renderAdminLayout(
            $this->renderSettingsContent($settings),
            'Einstellungen'
        );
    }
    
    /**
     * Render settings content
     *
     * @param array $settings
     * @return string
     */
    private function renderSettingsContent(array $settings): string
    {
        ob_start();
        include __DIR__ . '/../../Views/admin/settings.php';
        return ob_get_clean();
    }

    /**
     * Render the admin layout
     *
     * @param string $content The main content to render
     * @param string $title The page title
     * @return string Complete HTML page
     */
    public function renderAdminLayout(string $content, string $title = 'Admin'): string
    {
        $adminMenu = [
            ['url' => '/admin', 'title' => 'Dashboard', 'icon' => '📊'],
            ['url' => '/admin/pages', 'title' => 'Seiten', 'icon' => '📄'],
            ['url' => '/admin/menu', 'title' => 'Menü', 'icon' => '📑'],
            ['url' => '/admin/sidebars', 'title' => 'Seitenbereiche', 'icon' => '📱'],
            ['url' => '/admin/files', 'title' => 'Dateien', 'icon' => '📁'],
            ['url' => '/admin/settings', 'title' => 'Einstellungen', 'icon' => '⚙️'],
        ];

        ob_start();
        include __DIR__ . '/../../Views/admin/layout.php';
        return ob_get_clean();
    }

    /**
     * Save menu structure
     *
     * @param string $menuType
     * @return string JSON response
     */
    public function saveMenu(string $menuType = 'main'): string
    {
        // Get the menu data from POST request
        $menuData = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (empty($menuData)) {
            return json_encode(['success' => false, 'message' => 'Keine Menüdaten erhalten']);
        }
        
        // Convert menu type
        $configMenuType = $menuType === 'main' ? 'main_menu' : 'footer_menu';
        
        // Save the menu structure
        $success = $this->adminService->saveMenuStructure($menuData, $configMenuType);
        
        return json_encode(['success' => $success, 'message' => $success ? 'Menü erfolgreich gespeichert' : 'Fehler beim Speichern']);
    }

    /**
     * Save settings
     *
     * @return string JSON response
     */
    public function saveSettings(): string
    {
        // Get the settings data from POST request
        $settingsData = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (empty($settingsData)) {
            return json_encode(['success' => false, 'message' => 'Keine Einstellungen erhalten']);
        }
        
        // Save the settings
        $success = $this->adminService->saveSettings($settingsData);
        
        return json_encode(['success' => $success, 'message' => $success ? 'Einstellungen erfolgreich gespeichert' : 'Fehler beim Speichern']);
    }

    /**
     * Save page content
     *
     * @param string $pageId
     * @return string JSON response
     */
    public function savePage(string $pageId): string
    {
        // Get the raw input data for debugging
        $rawInput = file_get_contents('php://input');
        error_log('Raw input received: ' . $rawInput);
        
        // Get the page data from POST request
        $pageData = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            error_log('Raw input that failed: ' . $rawInput);
            return json_encode(['success' => false, 'message' => 'Ungültige JSON-Daten: ' . json_last_error_msg()]);
        }
        
        if (empty($pageData)) {
            error_log('Empty page data received');
            return json_encode(['success' => false, 'message' => 'Keine Seitendaten erhalten']);
        }
        
        // Debug: Log the received data
        error_log('Page data received: ' . print_r($pageData, true));
        
        // Find the existing page or create a new one if pageId is 'new'
        if ($pageId === 'new') {
            // Get the ID from the query parameter
            $newPageId = $_GET['id'] ?? 'page-' . time();
            
            // Create a new page with the specified ID
            $existingPage = new Page(
                $newPageId,
                $pageData['title'] ?? 'Neue Seite',
                $pageData['content'] ?? '<p><br></p>',
                'markdown',
                [], // sidebarLeft - leer für neue Seiten
                [], // sidebarRight - leer für neue Seiten
                new \DateTimeImmutable(),
                new \DateTimeImmutable()
            );
            
            error_log('Creating new page with ID: ' . $newPageId);
        } else {
            // Check if this is a new page with a custom ID
            $existingPage = $this->pageRepository->findById($pageId);
            
            if (!$existingPage) {
                // If page doesn't exist and ID doesn't start with 'new-page-', treat it as a new page
                if (strpos($pageId, 'new-page-') !== 0) {
                    // Create a new page with the specified ID
                    $existingPage = new Page(
                        $pageId,
                        $pageData['title'] ?? 'Neue Seite',
                        $pageData['content'] ?? '<p><br></p>',
                        'markdown',
                        [], // sidebarLeft - leer für neue Seiten
                        [], // sidebarRight - leer für neue Seiten
                        new \DateTimeImmutable(),
                        new \DateTimeImmutable()
                    );
                    
                    error_log('Creating new page with custom ID: ' . $pageId);
                } else {
                    error_log('Page not found: ' . $pageId);
                    return json_encode(['success' => false, 'message' => 'Seite nicht gefunden']);
                }
            } else {
                // Update the existing page
                $existingPage->setTitle($pageData['title'] ?? $existingPage->getTitle());
                $existingPage->setContent($pageData['content'] ?? $existingPage->getContent());
            }
        }
        
        // Debug: Log what we're about to save
        error_log('About to save page: ' . $existingPage->getTitle());
        error_log('Content length: ' . strlen($existingPage->getContent()));
        
        // Save the page - use the appropriate format
        $format = $existingPage->getContentType() === 'json' ? 'json' : 'markdown';
        $success = $this->pageRepository->save($existingPage, $format);
        
        return json_encode(['success' => $success, 'message' => $success ? 'Seite erfolgreich gespeichert' : 'Fehler beim Speichern']);
    }
    
    /**
     * Show new page form
     */
    public function newPage()
    {
        // Get the ID from the query parameter
        $pageId = $_GET['id'] ?? 'new-page-' . time();
        
        // Create a new empty page
        $page = new Page(
            $pageId,
            'Neue Seite',
            '<p><br></p>',
            'markdown',
            [], // sidebarLeft - leer für neue Seiten
            [], // sidebarRight - leer für neue Seiten
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
        
        // Render the edit page view with the new page
        echo $this->renderAdminLayout(
            $this->renderEditPageContent($page, 'new'),
            'Neue Seite erstellen: ' . htmlspecialchars($pageId)
        );
    }
    
    /**
     * Delete a page
     *
     * @param string $pageId
     */
    public function deletePage(string $pageId)
    {
        // Find the page
        $page = $this->pageRepository->findById($pageId);
        
        if (!$page) {
            echo "Seite nicht gefunden";
            return;
        }
        
        // Delete the page
        $success = $this->pageRepository->delete($page);
        
        if ($success) {
            // Redirect back to pages list
            header('Location: /admin/pages');
            exit;
        } else {
            echo "Fehler beim Löschen der Seite";
        }
    }
    
    /**
     * Show edit page form
     *
     * @param string $pageId
     */
    public function editPage(string $pageId)
    {
        // Find the page
        $page = $this->pageRepository->findById($pageId);
        
        if (!$page) {
            echo "Seite nicht gefunden";
            return;
        }
        
        // Render the edit page view
        echo $this->renderAdminLayout(
            $this->renderEditPageContent($page, $pageId),
            'Seite bearbeiten'
        );
    }
    
    /**
     * Edit global sidebars
     */
    public function editSidebars()
    {
        echo $this->renderAdminLayout(
            $this->renderEditSidebarsContent(),
            'Globale Seitenbereiche bearbeiten'
        );
    }
    
    /**
     * Save global sidebars
     */
    public function saveSidebars(): string
    {
        // Get the sidebar data from POST request
        $sidebarData = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (empty($sidebarData)) {
            return json_encode(['success' => false, 'message' => 'Keine Seitenbereichsdaten erhalten']);
        }
        
        // Save the global sidebars
        $success = $this->sidebarService->updateSidebars(
            $sidebarData['sidebarLeft'] ?? [],
            $sidebarData['sidebarRight'] ?? []
        );
        
        return json_encode(['success' => $success, 'message' => $success ? 'Globale Seitenbereiche erfolgreich gespeichert' : 'Fehler beim Speichern']);
    }
    
    /**
     * Render edit sidebars content
     *
     * @return string
     */
    private function renderEditSidebarsContent(): string
    {
        $sidebarLeft = $this->sidebarService->getLeftSidebar();
        $sidebarRight = $this->sidebarService->getRightSidebar();
        
        ob_start();
        include __DIR__ . '/../../Views/admin/edit_sidebars.php';
        return ob_get_clean();
    }
    
    /**
     * Render edit page content
     *
     * @param mixed $page
     * @param string $pageId
     * @return string
     */
    private function renderEditPageContent($page, $pageId): string
    {
        ob_start();
        include __DIR__ . '/../../Views/admin/edit_page.php';
        return ob_get_clean();
    }

    /**
     * Authentication stub (to be implemented later)
     *
     * @return bool Always returns true for now (development mode)
     */
    public function checkAuth(): bool
    {
        // TODO: Implement proper authentication
        // For now, always return true during development
        return true;
    }
}