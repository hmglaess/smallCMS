<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use App\Controllers\HomeController;
use App\Models\PageRepository;

use function DI\value;
use function DI\create;

// <-- wichtig für scalar/array Werte

require_once __DIR__ . '/../vendor/autoload.php';

/* --------------------------------------------------------------
 * 1. Konfiguration laden (aus config/config.json.dist)
 * -------------------------------------------------------------- */
$configPath     = __DIR__ . '/../config/config.json';
$distConfigPath = __DIR__ . '/../config/config.json.dist';

if (file_exists($configPath)) {
    $config = json_decode((string) file_get_contents($configPath), true);
} elseif (file_exists($distConfigPath)) {
    $config = json_decode((string) file_get_contents($distConfigPath), true);
} else {
    die('FATAL ERROR: Configuration file not found.');
}

/* --------------------------------------------------------------
 * 2. Session starten (falls noch nicht gestartet)
 * -------------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* --------------------------------------------------------------
 * 3. DI‑Container bauen
 * -------------------------------------------------------------- */
$builder = new ContainerBuilder();

/* 3a. Definitions – Config‑Array als fester Wert registrieren */
$builder->addDefinitions([
    // Das komplette Config‑Array wird als fester Wert bereitgestellt
    'config' => value($config),

    // PageRepository definieren
    PageRepository::class => create(PageRepository::class)
        ->constructor(__DIR__ . '/../pages'),

    // Definition für den SidebarService
    App\Services\SidebarService::class => \DI\create(App\Services\SidebarService::class)
        ->constructor(__DIR__ . '/../config/sidebars.json'),
    
    // Explizite Definition für den HomeController, um das Problem mit der Injektion zu beheben
    HomeController::class => \DI\factory(function (ContainerInterface $c) {
        return new HomeController($c->get('config'), $c->get(PageRepository::class), $c->get('App\\Services\\NavigationService'), $c->get('App\\Services\\SidebarService'));
    }),
    
    // Definition für den NavigationService
    App\Services\NavigationService::class => \DI\create(App\Services\NavigationService::class)
        ->constructor(\DI\get(PageRepository::class)),
    
    // Definition für den ApiController mit NavigationService
    App\Controllers\ApiController::class => \DI\create(App\Controllers\ApiController::class)
        ->constructor(\DI\get(App\Services\NavigationService::class)),
    
    // Admin Service Definition
    App\Services\Admin\AdminService::class => \DI\create(App\Services\Admin\AdminService::class),
    
    // Admin Controller Definition
    App\Controllers\Admin\AdminController::class => \DI\factory(function (ContainerInterface $c) {
        return new App\Controllers\Admin\AdminController(
            $c->get(PageRepository::class),
            $c->get(App\Services\NavigationService::class),
            $c->get(App\Services\Admin\AdminService::class),
            $c->get('config'),
            $c->get('App\\Services\\SidebarService')
        );
    }),
]);

// Beispiel‑Definition für PDO (auskommentiert, falls du später DB‑Zugriff brauchst)
// $builder->addDefinitions([
//     PDO::class => function (Psr\Container\ContainerInterface $c) {
//         $db = $c->get('config')['db'];
//         $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
//         return new PDO($dsn, $db['user'], $db['pass'], [
//             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::ATTR_EMULATE_PREPARES => false,
//         ]);
//     },
// ]);

/* 3b. Autowiring nur für Klassen aktivieren, die keine spezifischen Definitionen benötigen */
$builder->useAutowiring(true);

/* --------------------------------------------------------------
 * 4. Container bauen und zurückgeben
 * -------------------------------------------------------------- */
try {
    $container = $builder->build();
} catch (Throwable $e) {
    die('FATAL ERROR: Could not build the DI container: ' . $e->getMessage());
}

return $container;
