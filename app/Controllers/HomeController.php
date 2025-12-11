<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index(): void
    {
        $appName = htmlspecialchars($this->config['app']['name'] ?? 'smallCMS');
        $appEnv  = htmlspecialchars($this->config['app']['env'] ?? 'n/a');

        $data = [
            'title'   => 'Welcome to ' . $appName,
            'content' => 'This is the homepage. Current environment: ' . $appEnv,
        ];

        $this->view('home', $data);
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
