<?php
include_once(__DIR__ . '/src/App/App.php');
include_once(__DIR__ . '/src/Web/routes.php');

use Utils\DB;
use App\App;


// create the App
$app = new App();
// register all routes
setupRoutes($app);
// run the App to service the request
$app->run();