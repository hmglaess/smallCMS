<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service for managing global sidebar content
 */
class SidebarService
{
    private array $sidebarConfig;
    private string $configFile;

    /**
     * SidebarService constructor
     *
     * @param string $configFile Path to the sidebar configuration file
     */
    public function __construct(string $configFile = __DIR__ . '/../../config/sidebars.json')
    {
        $this->configFile = $configFile;
        $this->sidebarConfig = $this->loadConfig();
    }

    /**
     * Load sidebar configuration from file
     *
     * @return array Sidebar configuration
     */
    private function loadConfig(): array
    {
        if (!file_exists($this->configFile)) {
            // Return default configuration if file doesn't exist
            return [
                'sidebarLeft' => [],
                'sidebarRight' => []
            ];
        }

        $content = file_get_contents($this->configFile);
        if ($content === false) {
            return [
                'sidebarLeft' => [],
                'sidebarRight' => []
            ];
        }

        $config = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Failed to parse sidebar config: ' . json_last_error_msg());
            return [
                'sidebarLeft' => [],
                'sidebarRight' => []
            ];
        }

        return $config ?? [
            'sidebarLeft' => [],
            'sidebarRight' => []
        ];
    }

    /**
     * Get left sidebar widgets
     *
     * @return array Left sidebar widgets
     */
    public function getLeftSidebar(): array
    {
        return $this->sidebarConfig['sidebarLeft'] ?? [];
    }

    /**
     * Get right sidebar widgets
     *
     * @return array Right sidebar widgets
     */
    public function getRightSidebar(): array
    {
        return $this->sidebarConfig['sidebarRight'] ?? [];
    }

    /**
     * Save sidebar configuration
     *
     * @param array $config Sidebar configuration to save
     * @return bool Success status
     */
    public function saveConfig(array $config): bool
    {
        $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonContent === false) {
            return false;
        }

        return file_put_contents($this->configFile, $jsonContent) !== false;
    }

    /**
     * Add a widget to the left sidebar
     *
     * @param array $widget Widget to add
     * @return bool Success status
     */
    public function addLeftWidget(array $widget): bool
    {
        $this->sidebarConfig['sidebarLeft'][] = $widget;
        return $this->saveConfig($this->sidebarConfig);
    }

    /**
     * Add a widget to the right sidebar
     *
     * @param array $widget Widget to add
     * @return bool Success status
     */
    public function addRightWidget(array $widget): bool
    {
        $this->sidebarConfig['sidebarRight'][] = $widget;
        return $this->saveConfig($this->sidebarConfig);
    }

    /**
     * Update sidebar configuration
     *
     * @param array $sidebarLeft New left sidebar widgets
     * @param array $sidebarRight New right sidebar widgets
     * @return bool Success status
     */
    public function updateSidebars(array $sidebarLeft, array $sidebarRight): bool
    {
        $this->sidebarConfig['sidebarLeft'] = $sidebarLeft;
        $this->sidebarConfig['sidebarRight'] = $sidebarRight;
        return $this->saveConfig($this->sidebarConfig);
    }
}
