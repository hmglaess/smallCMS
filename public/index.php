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
    // Route für die Startseite
    $r->addRoute('GET', '/', 'App\Controllers\HomeController@index');
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
            $controller->$method(...array_values($vars));
        } catch (Throwable $e) {
            // Fehler beim Instantiieren/Ausführen des Controllers
            header('HTTP/1.1 500 Internal Server Error');
            // DEBUG‑Ausgabe nur in der Entwicklungsumgebung:
            echo '<pre>' . $e . '</pre>';
            echo 'An error occurred while processing your request.';
        }
        break;
}
