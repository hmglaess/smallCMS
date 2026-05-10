<?php

namespace App\Controllers;

use HTMX\HTMX;
use App\Services\NavigationService;

/**
 * Controller for handling API requests
 */
class ApiController
{
    private NavigationService $navigationService;
    
    /**
     * ApiController constructor
     *
     * @param NavigationService $navigationService Service for generating navigation
     */
    public function __construct(NavigationService $navigationService)
    {
        $this->navigationService = $navigationService;
    }
    
    /**
     * Generate navigation HTML
     *
     * Outputs HTML list items for the main navigation menu
     */
    public function navigation()
    {
        $html = $this->navigationService->generateNavigationHtml('main_menu');
        
        echo $html;
        exit;
    }
    
    /**
     * Generate events HTML
     *
     * Outputs HTML for upcoming events with images
     */
    public function events()
    {
        $events = [
            [
                'date' => '10. Januar 2026',
                'title' => 'Queens 45 BC',
                'image' => '/assets/img/Catharina_DiscoFoxSpecialParty_4_50.png',
                'description' => 'Discofox Special Party'
            ]
            // Weitere Events können hier hinzugefügt werden
        ];
        
        // HTML direkt generieren
        $event = $events[0];
        $html = '<p>' . htmlspecialchars($event['date']) . '<br>';
        $html .= htmlspecialchars($event['title']) . '</p>';
        $html .= '<div class="image-container">';
        $html .= '<img id="myImage" src="' . htmlspecialchars($event['image']) . '" alt="Klick mich zum Vergrößern">';
        $html .= '</div>';
        echo $html;
        exit;
    }
    
    /**
     * Generate dynamic content for a page
     *
     * Outputs HTML content with page information and timestamp
     */
    public function content()
    {
        $page = $_GET['page'] ?? 'start';
        
        // Hier könnte man den Inhalt aus einer Datenbank oder Dateien laden
        $content = '<p>Dies ist dynamischer Inhalt für Seite: ' . htmlspecialchars($page) . '</p>';
        $content .= '<p>Geladen mit HTMX am: ' . date('Y-m-d H:i:s') . '</p>';
        
        echo $content;
        exit;
    }
}