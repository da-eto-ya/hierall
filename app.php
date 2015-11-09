<?php
/**
 * Основной файл приложения.
 */

use Herrera\Pdo\PdoServiceProvider;
use Hierall\CatalogueRepository;
use Knp\Provider\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();
$config = require_once __DIR__ . '/config/config.php';


// Services

// PDO instance
$app->register(new PdoServiceProvider(), [
    'pdo.dsn' => $config['pdo']['dsn'],
]);

/** @var PDO $pdo */
$pdo = $app['pdo'];

// Twig
$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/views',
]);

// Console
$app->register(new ConsoleServiceProvider(), array(
    'console.name' => 'Hierall',
    'console.version' => '1.0.0',
    'console.project_directory' => __DIR__
));

// Catalogue Repository
$app['hierall.catalogues'] = $app->share(function ($app) {
    return new CatalogueRepository($app['pdo']);
});


return $app;