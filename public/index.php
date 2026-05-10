<?php

/**
 * smallCMS – Front‑Controller (public/index.php)
 */

declare(strict_types=1);

// ---------------------------------------------------------------
// 1. Bootstrap – DI‑Container holen
// ---------------------------------------------------------------
try {
    // public/ liegt eine Ebene tiefer als bootstrap/
    $container = require_once __DIR__ . '/../bootstrap/bootstrap.php';
} catch (Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Critical error while bootstrapping the application.');
}

// ---------------------------------------------------------------
// 2. Router‑Setup (FastRoute)
// ---------------------------------------------------------------
use function FastRoute\simpleDispatcher;   // Funktionsimport
use FastRoute\Dispatcher;                  // Konstanten‑Import

$dispatcher = simpleDispatcher(function (FastRoute\RouteCollector $r) {
    // Admin-Routen (hohe Priorität)
    $r->addRoute('GET', '/admin', 'App\Controllers\Admin\AdminController@dashboard');
    $r->addRoute('GET', '/admin/pages', 'App\Controllers\Admin\AdminController@pages');
    $r->addRoute('GET', '/admin/menu', 'App\Controllers\Admin\AdminController@menu');
    $r->addRoute('GET', '/admin/files', 'App\Controllers\Admin\AdminController@files');
    $r->addRoute('GET', '/admin/settings', 'App\Controllers\Admin\AdminController@settings');
    
    // Admin-Speicherfunktionen
    $r->addRoute('POST', '/admin/menu/save/{menuType}', 'App\Controllers\Admin\AdminController@saveMenu');
    $r->addRoute('POST', '/admin/settings/save', 'App\Controllers\Admin\AdminController@saveSettings');
    $r->addRoute('POST', '/admin/pages/save/{pageId}', 'App\Controllers\Admin\AdminController@savePage');
    
    // Admin-Seitenverwaltung
    $r->addRoute('GET', '/admin/pages/new', 'App\Controllers\Admin\AdminController@newPage');
    $r->addRoute('GET', '/admin/pages/{pageId}/edit', 'App\Controllers\Admin\AdminController@editPage');
    $r->addRoute('GET', '/admin/sidebars', 'App\Controllers\Admin\AdminController@editSidebars');
    $r->addRoute('POST', '/admin/save-sidebars', 'App\Controllers\Admin\AdminController@saveSidebars');
    $r->addRoute('GET', '/admin/pages/{pageId}/delete', 'App\Controllers\Admin\AdminController@deletePage');
    
    // Admin-Dateiverwaltungsfunktionen
    $r->addRoute('POST', '/admin/files/upload', 'App\Controllers\Admin\AdminController@uploadFile');
    $r->addRoute('POST', '/admin/files/create-folder', 'App\Controllers\Admin\AdminController@createFolder');
    $r->addRoute('POST', '/admin/files/delete', 'App\Controllers\Admin\AdminController@deleteFile');
    
    // Route für die Startseite
    $r->addRoute('GET', '/', 'App\Controllers\HomeController@index');
    // Route für einzelne Seiten
    $r->addRoute('GET', '/page/{id}', 'App\Controllers\HomeController@showPage');
    
    // API-Routen für HTMX
    $r->addRoute('GET', '/api/navigation', 'App\Controllers\ApiController@navigation');
    $r->addRoute('GET', '/api/events', 'App\Controllers\ApiController@events');
    $r->addRoute('GET', '/api/content', 'App\Controllers\ApiController@content');
    
    // weitere Routen können hier ergänzt werden …
});

// ---------------------------------------------------------------
// 3. Request‑Dispatch
// ---------------------------------------------------------------
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri        = $_SERVER['REQUEST_URI'];

// Query‑String entfernen & URI dekodieren
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 – Page Not Found';
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 – Method Not Allowed';
        break;

    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars    = $routeInfo[2];

        [$class, $method] = explode('@', $handler);

        try {
            /** @var object $controller */
            $controller = $container->get($class);
            $result = $controller->$method(...array_values($vars));
            
            // Wenn die Methode eine Antwort zurückgibt, diese ausgeben
            if ($result !== null) {
                // Setze den Content-Type-Header für JSON-Antworten
                if (strpos($result, '{') === 0 || strpos($result, '[') === 0) {
                    header('Content-Type: application/json');
                }
                echo $result;
            }
        } catch (Throwable $e) {
            // Fehler beim Instantiieren/Ausführen des Controllers
            header('HTTP/1.1 500 Internal Server Error');
            // DEBUG‑Ausgabe nur in der Entwicklungsumgebung:
            echo '<pre>' . $e . '</pre>';
            echo 'An error occurred while processing your request.';
        }
        break;
}
