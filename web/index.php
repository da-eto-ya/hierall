<?php

use Herrera\Pdo\PdoServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$config = require_once __DIR__ . '/../config/config.php';

// PDO instance
$app->register(
    new PdoServiceProvider(),
    [
        'pdo.dsn' => $config['pdo']['dsn'],
    ]
);
/** @var PDO $pdo */
$pdo = $app['pdo'];

// Routes

// Mainpage
$app->get('/', function () {
    return 'Hello';
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
