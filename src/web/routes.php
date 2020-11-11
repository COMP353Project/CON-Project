<?php

include_once(__DIR__ . "/../app/App.php");
include_once(__DIR__ . "/../web/dbAPI.php");

use app\App;

function setupRoutes($app) {

    /* @var $app App */
    $app->get('/users', 'web\\dbAPI\\getUsers');
}