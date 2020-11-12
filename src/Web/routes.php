<?php

include_once(__DIR__ . "/../App/App.php");
include_once(__DIR__ . "/../Web/DbAPI.php");
include_once(__DIR__ . "/../Web/PageRenderers.php");

use App\App;


/**
 * Register all the routes to for the app
 *
 * @var $app App
 */
function setupRoutes($app) {

    $app->get('/', 'Web\\PageRenderers\\renderHomePage');

    $app->get('/users', 'Web\\DbAPI\\getUsers');

    $app->get('/createaccount', "Web\\PageRenderers\\renderSignUp");
    $app->post('/createaccount', 'Web\\DbAPI\\signUp');
}