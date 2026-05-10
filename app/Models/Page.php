<?php

declare(strict_types=1);

namespace App\Models;


/**
 * Class for generating pages
 */
class Page
{
    private string $id;
    private string $title;
    private string $content;
    private string $contentType; // 'json' or 'markdown'
    private array $sidebarLeft; // Left sidebar content
    private array $sidebarRight; // Right sidebar content
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * Page constructor
     *
     * @param string $id Unique page identifier
     * @param string $title Page title
     * @param string $content Page content
     * @param string $contentType Content type (json, markdown, html, etc.)
     * @param array $sidebarLeft Left sidebar content
     * @param array $sidebarRight Right sidebar content
     * @param \DateTimeImmutable|null $createdAt Creation timestamp
     * @param \DateTimeImmutable|null $updatedAt Last update timestamp
     */
    public function __construct(
        string $id,
        string $title,
        string $content,
        string $contentType = 'markdown',
        array $sidebarLeft = [],
        array $sidebarRight = [],
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->sidebarLeft = $sidebarLeft;
        $this->sidebarRight = $sidebarRight;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    // Getter
    /**
     * Get page ID
     *
     * @return string Page identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get page title
     *
     * @return string Page title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get page content
     *
     * @return string Page content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get content type
     *
     * @return string Content type (json, markdown, html, etc.)
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Get creation timestamp
     *
     * @return \DateTimeImmutable When the page was created
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get last update timestamp
     *
     * @return \DateTimeImmutable When the page was last updated
     */
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Getter for sidebars
    /**
     * Get left sidebar content
     *
     * @return array Left sidebar content
     */
    public function getSidebarLeft(): array
    {
        return $this->sidebarLeft;
    }

    /**
     * Get right sidebar content
     *
     * @return array Right sidebar content
     */
    public function getSidebarRight(): array
    {
        return $this->sidebarRight;
    }

    // setContent Methode zum Aktualisieren des Inhalts
    /**
     * Set page content
     *
     * @param string $content New content for the page
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    // setTitle Methode zum Aktualisieren des Titels
    /**
     * Set page title
     *
     * @param string $title New title for the page
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Set left sidebar content
     *
     * @param array $content New content for the left sidebar
     */
    public function setSidebarLeft(array $content): void
    {
        $this->sidebarLeft = $content;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Set right sidebar content
     *
     * @param array $content New content for the right sidebar
     */
    public function setSidebarRight(array $content): void
    {
        $this->sidebarRight = $content;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Add a widget to the left sidebar
     *
     * @param array $widget Widget to add
     */
    public function addLeftWidget(array $widget): void
    {
        $this->sidebarLeft[] = $widget;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Add a widget to the right sidebar
     *
     * @param array $widget Widget to add
     */
    public function addRightWidget(array $widget): void
    {
        $this->sidebarRight[] = $widget;
        $this->updatedAt = new \DateTimeImmutable();
    }
}