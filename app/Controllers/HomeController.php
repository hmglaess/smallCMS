<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PageRepository;
use App\Services\NavigationService;
use HTMX\HTMX;
use App\Services\SidebarService;

/**
 * Controller for handling home page and general page requests
 */
class HomeController
{
    private array $config;
    private PageRepository $pageRepository;
    private NavigationService $navigationService;
    private SidebarService $sidebarService;

    /**
     * HomeController constructor
     *
     * @param array $config Application configuration
     * @param PageRepository $pageRepository Repository for page data access
     * @param NavigationService $navigationService Service for generating navigation
     * @param SidebarService $sidebarService Service for managing sidebars
     */
    public function __construct(array $config, PageRepository $pageRepository, NavigationService $navigationService, SidebarService $sidebarService)
    {
        $this->config = $config;
        $this->pageRepository = $pageRepository;
        $this->navigationService = $navigationService;
        $this->sidebarService = $sidebarService;
    }

    /**
     * Display the home page
     *
     * Loads the default 'start' page and renders it using the template view
     */
    public function index(): void
    {
        // Standardseite laden (z. B. "start")
        $page = $this->pageRepository->findById('start');

        if (!$page) {
            http_response_code(404);
            echo '404 – Page Not Found';
            return;
        }

        // Site-Name aus der Konfiguration verwenden
        $siteName = $this->config['app']['name'] ?? 'smallCMS';

        $data = [
            'title' => htmlspecialchars($page->getTitle()) . ' | ' . htmlspecialchars($siteName),
            'content' => $this->renderContent($page),
            'siteName' => htmlspecialchars($siteName),
            'navigationService' => $this->navigationService,
            'currentPageId' => $page->getId(),
            'page' => $page,
            'sidebarService' => $this->sidebarService,
        ];

        // Überprüfen, ob die Anfrage von HTMX kommt
        if (HTMX::isRequest()) {
            // Nur den Inhalt zurückgeben für HTMX-Anfragen
            echo $data['content'];
            exit;
        } else {
            // Volle Seite für normale Anfragen rendern
            $this->view('template', $data);
        }
    }

    /**
     * Display a specific page by ID
     *
     * @param string $id The page ID to display
     */
    public function showPage(string $id): void
    {
        $page = $this->pageRepository->findById($id);

        if (!$page) {
            if (HTMX::isRequest()) {
                echo '<div class="error">404 – Page Not Found</div>';
                exit;
            } else {
                http_response_code(404);
                echo '404 – Page Not Found';
            }
            return;
        }

        // Site-Name aus der Konfiguration verwenden
        $siteName = $this->config['app']['name'] ?? 'smallCMS';

        $data = [
            'title' => htmlspecialchars($page->getTitle()) . ' | ' . htmlspecialchars($siteName),
            'content' => $this->renderContent($page),
            'siteName' => htmlspecialchars($siteName),
            'navigationService' => $this->navigationService,
            'currentPageId' => $page->getId(),
            'sidebarService' => $this->sidebarService,
        ];

        // Überprüfen, ob die Anfrage von HTMX kommt
        if (HTMX::isRequest()) {
            // URL in der Browser-History aktualisieren
            HTMX::pushUrl("/page/{$id}");
            // Nur den Inhalt zurückgeben für HTMX-Anfragen
            echo $data['content'];
            exit;
        } else {
            // Volle Seite für normale Anfragen rendern
            $this->view('template', $data);
        }
    }

    /**
     * Render page content based on content type
     *
     * Converts markdown to HTML or returns content as-is depending on content type
     *
     * @param mixed $page The page object containing content to render
     * @return string Rendered HTML content
     */
    private function renderContent($page): string
    {
        if ($page->getContentType() === 'markdown') {
            // Einfache Markdown-zu-HTML-Umwandlung
            $content = $page->getContent();
            // Ersetze einfache Markdown-Elemente
            $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
            $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
            $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
            $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
            $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
            $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
            $content = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $content);
            $content = preg_replace('/^- (.*$)/m', '<li>$1</li>', $content);
            $content = nl2br($content);
            return $content;
        } elseif ($page->getContentType() === 'html') {
            // HTML-Inhalt direkt zurückgeben, ohne Escaping
            return $page->getContent();
        } else {
            // Für JSON oder reinen Text einfach ausgeben
            return htmlspecialchars($page->getContent());
        }
    }

    /**
     * Lädt eine View aus app/Views/
     *
     * @param string $view Name der View-Datei (ohne .php‑Endung)
     * @param array  $data Daten, die in der View als Variablen verfügbar sein sollen
     */
    private function view(string $view, array $data = []): void
    {
        // Variablen aus $data im Scope der View verfügbar machen
        extract($data);

        // __DIR__ = …/app/Controllers
        // Wir gehen eine Ebene nach oben zu …/app und dann in den Ordner Views
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            // Klare Ausnahme, die im Debug‑Output sichtbar wird
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        require $viewFile;
    }
}
