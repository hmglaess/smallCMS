<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Repository for managing page data storage and retrieval
 */
class PageRepository
{
    private string $pagesDirectory;

    /**
     * PageRepository constructor
     *
     * @param string $pagesDirectory Directory where page files are stored
     * @throws \InvalidArgumentException If the pages directory does not exist
     */
    public function __construct(string $pagesDirectory)
    {
        $this->pagesDirectory = rtrim($pagesDirectory, '/');

        // Stelle sicher, dass das Verzeichnis existiert
        if (!is_dir($this->pagesDirectory)) {
            throw new \InvalidArgumentException("Pages directory does not exist: {$this->pagesDirectory}");
        }
    }

    /**
     * Lade eine Seite anhand ihrer ID
     *
     * @param string $id Die ID der Seite (entspricht dem Dateinamen ohne Erweiterung)
     * @return Page|null Die Seite oder null, wenn sie nicht gefunden wurde
     */
    public function findById(string $id): ?Page
    {
        // Suche nach der Datei mit dieser ID (entweder .json oder .md)
        $filePath = $this->pagesDirectory . '/' . $id;

        if (file_exists($filePath . '.json')) {
            return $this->loadFromJsonFile($id, $filePath . '.json');
        } elseif (file_exists($filePath . '.md')) {
            return $this->loadFromMarkdownFile($id, $filePath . '.md');
        }

        return null;
    }

    /**
     * Lade alle verfügbaren Seiten
     *
     * @return Page[]
     */
    public function findAll(): array
    {
        $pages = [];

        // Durchsuche das Verzeichnis nach .json und .md Dateien
        $files = array_diff(scandir($this->pagesDirectory), array('.', '..'));

        foreach ($files as $file) {
            $pathinfo = pathinfo($this->pagesDirectory . '/' . $file);
            $extension = $pathinfo['extension'] ?? '';
            $filename = $pathinfo['filename'];

            if ($extension === 'json') {
                $page = $this->loadFromJsonFile($filename, $this->pagesDirectory . '/' . $file);
            } elseif ($extension === 'md') {
                $page = $this->loadFromMarkdownFile($filename, $this->pagesDirectory . '/' . $file);
            } else {
                continue; // Unbekannte Dateiendung
            }

            if ($page) {
                $pages[$page->getId()] = $page;
            }
        }

        return $pages;
    }

    /**
     * Speichere eine Seite in einer Datei
     *
     * @param Page $page Die zu speichernde Seite
     * @param string $format Das gewünschte Format ('json' oder 'markdown')
     * @return bool Erfolgsmeldung
     */
    public function save(Page $page, string $format = 'markdown'): bool
    {
        $filePath = $this->pagesDirectory . '/' . $page->getId();

        if ($format === 'json') {
            $data = [
                'id' => $page->getId(),
                'title' => $page->getTitle(),
                'content' => $page->getContent(),
                'contentType' => $page->getContentType(),
                'sidebarLeft' => $page->getSidebarLeft(),
                'sidebarRight' => $page->getSidebarRight(),
                'createdAt' => $page->getCreatedAt()->format('c'),
                'updatedAt' => $page->getUpdatedAt()->format('c'),
            ];

            $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filePath .= '.json';
        } else { // Standardmäßig Markdown
            // Bei Markdown-Format speichern wir Titel und Inhalt
            // Immer den kompletten Inhalt ersetzen (für die Bearbeitung)
            $content = "# " . $page->getTitle() . "\n\n" . $page->getContent();
            $filePath .= '.md';
        }

        return file_put_contents($filePath, $content) !== false;
    }

    /**
     * Lösche eine Seite
     *
     * @param string $id Die ID der zu löschenden Seite
     * @return bool Erfolgsmeldung
     */
    public function deleteById(string $id): bool
    {
        // Finde die Datei, egal ob .json oder .md
        if (file_exists($this->pagesDirectory . '/' . $id . '.json')) {
            return unlink($this->pagesDirectory . '/' . $id . '.json');
        } elseif (file_exists($this->pagesDirectory . '/' . $id . '.md')) {
            return unlink($this->pagesDirectory . '/' . $id . '.md');
        }

        return false; // Datei existierte nicht
    }

    /**
     * Lösche eine Seite
     *
     * @param Page $page Die zu löschende Seite
     * @return bool Erfolgsmeldung
     */
    public function delete(Page $page): bool
    {
        return $this->deleteById($page->getId());
    }

    /**
     * Load page from JSON file
     *
     * @param string $id Page identifier
     * @param string $filePath Path to JSON file
     * @return Page|null Page object or null if loading failed
     */
    private function loadFromJsonFile(string $id, string $filePath): ?Page
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }

        // Validiere benötigte Felder
        if (!isset($data['title'])) {
            return null;
        }

        // Content kann aus JSON oder aus separater Markdown-Datei kommen
        $pageContent = $data['content'] ?? '';
        $contentType = $data['content_type'] ?? $data['contentType'] ?? 'json';
        
        // Wenn kein Content in JSON und eine Markdown-Datei existiert, lade den Inhalt daraus
        if (empty($pageContent) && file_exists($this->pagesDirectory . '/' . $id . '.md')) {
            $mdContent = file_get_contents($this->pagesDirectory . '/' . $id . '.md');
            if ($mdContent !== false) {
                $pageContent = $mdContent;
                $contentType = 'markdown';
            }
        }

        return new Page(
            $data['id'] ?? $id,
            $data['title'],
            $pageContent,
            $contentType,
            $data['sidebarLeft'] ?? [],
            $data['sidebarRight'] ?? [],
            isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null
        );
    }

    /**
     * Load page from Markdown file
     *
     * @param string $id Page identifier
     * @param string $filePath Path to Markdown file
     * @return Page|null Page object or null if loading failed
     */
    private function loadFromMarkdownFile(string $id, string $filePath): ?Page
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        // Versuche, den Titel aus der ersten Überschrift zu extrahieren
        $firstLine = strtok($content, "\n");
        $title = $firstLine;

        if (str_starts_with($firstLine, '# ')) {
            $title = trim(substr($firstLine, 2)); // Entferne "# " am Anfang
            // Entferne die erste Zeile vom Inhalt
            $content = substr($content, strlen($firstLine) + 1);
        } elseif (preg_match('/^#\s+(.+)$/', $firstLine, $matches)) {
            $title = trim($matches[1]);
            // Entferne die erste Zeile vom Inhalt
            $content = substr($content, strlen($firstLine) + 1);
        }

        // Bestimme Änderungsdatum aus Dateieigenschaften
        $modifiedTime = filemtime($filePath);
        $updatedAt = $modifiedTime ? new \DateTimeImmutable('@' . $modifiedTime) : null;

        return new Page(
            $id,
            $title,
            $content,
            'markdown',
            [],  // sidebarLeft - leer für Markdown-Seiten
            [],  // sidebarRight - leer für Markdown-Seiten
            null,  // createdAt kann aus Dateieigenschaften ermittelt werden, wenn benötigt
            $updatedAt
        );
    }
}