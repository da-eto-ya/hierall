<?php

use Herrera\Pdo\PdoServiceProvider;
use Hierall\CatalogueRepository;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$config = require_once __DIR__ . '/../config/config.php';


// Services

// PDO instance
$app->register(new PdoServiceProvider(), [
    'pdo.dsn' => $config['pdo']['dsn'],
]);

/** @var PDO $pdo */
$pdo = $app['pdo'];

// Twig
$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views',
]);

// Catalogue Repository
$app['hierall.catalogues'] = $app->share(function ($app) {
    return new CatalogueRepository($app['pdo']);
});


// Routes

// Mainpage
$app->get('/', function () use ($app) {
    return $app['twig']->render('mainpage.twig');
});

// Fetch catalogues
$app->post('/ajax/fetchCatalogues', function () use ($app) {
    $catalogues = $app['hierall.catalogues']->fetchRootCatalogues();

    return $app->json($catalogues);
});

// Testing routes
$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello ' . $app->escape($name) . ". Router works as expected.";
});

// Testing PDO
$app->get('/pg', function () use ($app, $pdo) {
    $rows = [];
    $res = $pdo->query("SELECT * FROM test");

    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row['id'] . ':' . $row['name'];
    }

    return join("<br>", $rows);
});


// Run
$app->run();
