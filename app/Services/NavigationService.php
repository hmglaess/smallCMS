<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PageRepository;

/**
 * Service for generating hierarchical navigation from pages and menu configuration
 */
class NavigationService
{
    private PageRepository $pageRepository;
    private array $menuConfig;
    
    /**
     * NavigationService constructor
     *
     * @param PageRepository $pageRepository Repository for accessing page data
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->menuConfig = $this->loadMenuConfiguration();
    }
    
    /**
     * Load menu configuration from file
     *
     * @return array Menu configuration
     */
    private function loadMenuConfiguration(): array
    {
        $menuConfigPath = __DIR__ . '/../../config/menu.json';
        $distMenuConfigPath = __DIR__ . '/../../config/menu.json.dist';
        
        if (file_exists($menuConfigPath)) {
            $menuConfig = json_decode((string) file_get_contents($menuConfigPath), true);
        } elseif (file_exists($distMenuConfigPath)) {
            $menuConfig = json_decode((string) file_get_contents($distMenuConfigPath), true);
        } else {
            // Fallback to default configuration
            $menuConfig = [
                'main_menu' => [
                    'structure' => [],
                    'settings' => [
                        'show_hidden_pages' => false,
                        'max_depth' => 2,
                        'sort_by_position' => true
                    ]
                ]
            ];
        }
        
        return $menuConfig ?? [];
    }
    
    /**
     * Generate navigation tree from menu configuration and available pages
     *
     * @param string $menuName Name of the menu to generate (e.g., 'main_menu')
     * @return array Navigation tree
     */
    public function generateNavigationTree(string $menuName = 'main_menu'): array
    {
        if (!isset($this->menuConfig[$menuName])) {
            error_log('Menu configuration not found: ' . $menuName);
            return [];
        }
        
        $menuConfig = $this->menuConfig[$menuName];
        $settings = $menuConfig['settings'] ?? [];
        $structure = $menuConfig['structure'] ?? [];
        
        error_log('Menu structure for ' . $menuName . ': ' . print_r($structure, true));
        
        // Get all available pages
        $pages = $this->pageRepository->findAll();
        
        error_log('Available pages: ' . print_r(array_keys($pages), true));
        
        // Build navigation tree
        $navigationTree = $this->buildNavigationTree($structure, $pages, $settings);
        
        error_log('Generated navigation tree: ' . print_r($navigationTree, true));
        
        return $navigationTree;
    }
    
    /**
     * Build navigation tree recursively
     *
     * @param array $structure Menu structure from configuration
     * @param array $pages Available pages
     * @param array $settings Menu settings
     * @param int $depth Current depth level
     * @return array Built navigation tree
     */
    private function buildNavigationTree(array $structure, array $pages, array $settings, int $depth = 0): array
    {
        $navigation = [];
        
        // Check if we've reached maximum depth
        if (isset($settings['max_depth']) && $depth >= $settings['max_depth']) {
            return [];
        }
        
        foreach ($structure as $item) {
            // Skip hidden items if not showing hidden pages
            if (isset($item['visible']) && !$item['visible'] && empty($settings['show_hidden_pages'])) {
                continue;
            }
            
            // Check if page exists
            $pageExists = isset($pages[$item['id']]);
            $page = $pageExists ? $pages[$item['id']] : null;
            
            // Use configured menu title if available, otherwise use page title
            $title = $item['title'] ?? ($page ? $page->getTitle() : $item['id']);
            
            // Check if this is a header item (for footer menu sections)
            $isHeader = !empty($item['is_header']);
            
            $navigationItem = [
                'id' => $item['id'],
                'title' => $title,
                'url' => $isHeader ? null : '/page/' . $item['id'],
                'page_exists' => $pageExists,
                'has_submenu' => !empty($item['submenu']),
                'submenu' => [],
                'position' => $item['position'] ?? 0,
                'depth' => $depth,
                'content_type' => $page ? $page->getContentType() : null,
                'is_header' => $isHeader
            ];
            
            // Build submenu recursively
            if (!empty($item['submenu'])) {
                $navigationItem['submenu'] = $this->buildNavigationTree(
                    $item['submenu'],
                    $pages,
                    $settings,
                    $depth + 1
                );
            }
            
            $navigation[] = $navigationItem;
        }
        
        // Sort by position if enabled
        if (!empty($settings['sort_by_position'])) {
            usort($navigation, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });
        }
        
        return $navigation;
    }
    
    /**
     * Generate HTML for hierarchical navigation
     *
     * @param string $menuName Name of the menu to generate
     * @param int $maxDepth Maximum depth to render
     * @return string HTML for navigation
     */
    public function generateNavigationHtml(string $menuName = 'main_menu', int $maxDepth = 2): string
    {
        $navigationTree = $this->generateNavigationTree($menuName);
        return $this->renderNavigationTree($navigationTree, $maxDepth);
    }
    
    /**
     * Render navigation tree as HTML
     *
     * @param array $navigationTree Navigation tree to render
     * @param int $maxDepth Maximum depth to render
     * @param int $currentDepth Current depth level
     * @return string HTML output
     */
    private function renderNavigationTree(array $navigationTree, int $maxDepth, int $currentDepth = 0): string
    {
        if ($currentDepth >= $maxDepth) {
            return '';
        }
        
        $html = '';
        
        foreach ($navigationTree as $item) {
            // Skip items without existing pages (optional), but keep header items
            // For footer menu (depth 0), show all items even if page doesn't exist
            if (!$item['page_exists'] && empty($item['is_header']) && $currentDepth > 0) {
                continue;
            }
            
            $hasSubmenu = $item['has_submenu'] && !empty($item['submenu']) && ($currentDepth + 1) < $maxDepth;
            
            // Handle header items differently (for footer menu sections)
            if (!empty($item['is_header'])) {
                $html .= '<li class="menu-header">' . htmlspecialchars($item['title']) . '</li>';
                continue;
            }
            
            $html .= '<li' . ($hasSubmenu ? ' class="has-submenu"' : '') . '>';
            $html .= '<a href="' . htmlspecialchars($item['url']) . '" '
                   . 'hx-get="' . htmlspecialchars($item['url']) . '" '
                   . 'hx-target="#main-content" '
                   . 'hx-push-url="true">' 
                   . htmlspecialchars($item['title']) 
                   . '</a>';
            
            // Render submenu if exists and within depth limit
            if ($hasSubmenu) {
                $html .= '<ul class="submenu">';
                $html .= $this->renderNavigationTree($item['submenu'], $maxDepth, $currentDepth + 1);
                $html .= '</ul>';
            }
            
            $html .= '</li>';
        }
        
        return $html;
    }
    
    /**
     * Get flat navigation list (for breadcrumbs, etc.)
     *
     * @param string $menuName Name of the menu
     * @return array Flat list of navigation items
     */
    public function getFlatNavigation(string $menuName = 'main_menu'): array
    {
        $navigationTree = $this->generateNavigationTree($menuName);
        return $this->flattenNavigationTree($navigationTree);
    }
    
    /**
     * Flatten navigation tree to simple array
     *
     * @param array $navigationTree Navigation tree
     * @param array $result Result array (by reference)
     * @return array Flat navigation items
     */
    private function flattenNavigationTree(array $navigationTree, array &$result = []): array
    {
        foreach ($navigationTree as $item) {
            $result[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'url' => $item['url'],
                'page_exists' => $item['page_exists'],
                'depth' => $item['depth']
            ];
            
            if ($item['has_submenu']) {
                $this->flattenNavigationTree($item['submenu'], $result);
            }
        }
        
        return $result;
    }
    
    /**
     * Get breadcrumb navigation for a specific page
     *
     * @param string $currentPageId The ID of the current page
     * @param string $menuName Name of the menu
     * @return array Breadcrumb navigation items
     */
    public function getBreadcrumbNavigation(string $currentPageId, string $menuName = 'main_menu'): array
    {
        $flatNavigation = $this->getFlatNavigation($menuName);
        $breadcrumbs = [];
        
        // Always include home page
        foreach ($flatNavigation as $item) {
            if ($item['id'] === 'start') {
                $breadcrumbs[] = [
                    'url' => $item['url'],
                    'title' => $item['title'],
                    'isCurrent' => ($item['id'] === $currentPageId)
                ];
                break;
            }
        }
        
        // Add parent pages if this is a subpage
        if (strpos($currentPageId, '-') !== false) {
            $parts = explode('-', $currentPageId);
            $parentId = $parts[0];
            
            foreach ($flatNavigation as $item) {
                if ($item['id'] === $parentId) {
                    $breadcrumbs[] = [
                        'url' => $item['url'],
                        'title' => $item['title'],
                        'isCurrent' => false
                    ];
                    break;
                }
            }
        }
        
        // Add current page
        foreach ($flatNavigation as $item) {
            if ($item['id'] === $currentPageId) {
                $breadcrumbs[] = [
                    'url' => $item['url'],
                    'title' => $item['title'],
                    'isCurrent' => true
                ];
                break;
            }
        }
        
        return $breadcrumbs;
    }
}