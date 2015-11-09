<?php

use Hierall\CatalogueRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = require_once __DIR__ . '/../app.php';

// Routes

// Mainpage
$app->get('/', function (Application $app) {
    return $app['twig']->render('mainpage.twig');
});

// Fetch catalogues
$app->post('/ajax/fetchCatalogues', function (Request $request, Application $app) {
    /** @var CatalogueRepository $catalogueRepository */
    $catalogueRepository = $app['hierall.catalogues'];
    $parentId = (int)$request->get('parentId', 0);

    if (!$parentId) {
        $catalogues = $catalogueRepository->fetchRootCatalogues();
        $parent = null;
    } else {
        $catalogues = $catalogueRepository->fetchChildrenCatalogues($parentId);
        $parent = $catalogueRepository->fetchParentNode($parentId);

        // top-level
        if (!$parent) {
            $parent = ['id' => 0, 'name' => '..'];
        }
    }

    return $app->json([
        'catalogues' => $catalogues,
        'parent' => $parent,
    ]);
});

// Remove catalogue
$app->post('/ajax/removeCatalogue', function (Request $request, Application $app) {
    /** @var CatalogueRepository $catalogueRepository */
    $catalogueRepository = $app['hierall.catalogues'];
    $catalogueId = (int)$request->get('catalogueId', 0);
    $success = false;

    if ($catalogueId) {
        $success = $catalogueRepository->removeCatalogue($catalogueId);
    }

    return $app->json([
        'success' => $success,
    ]);
});

// Edit catalogue (rename)
$app->post('/ajax/renameCatalogue', function (Request $request, Application $app) {
    /** @var CatalogueRepository $catalogueRepository */
    $catalogueRepository = $app['hierall.catalogues'];
    $catalogueId = (int)$request->get('catalogueId', 0);
    $name = $request->get('name', '');
    $success = false;

    if ($catalogueId) {
        $success = $catalogueRepository->renameCatalogue($catalogueId, $name);
    }

    return $app->json([
        'success' => $success,
    ]);
});


// Run
$app->run();
