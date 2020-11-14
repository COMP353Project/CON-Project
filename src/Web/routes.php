<?php

include_once(__DIR__ . "/../App/App.php");
include_once(__DIR__ . "/../Web/DbAPI.php");
include_once(__DIR__ . "/../Web/PageRenderers.php");
include_once(__DIR__ . "/../Http/Request.php");

use App\App;
use Http\Request;
use Web\DbAPI;

/**
 * Register all the routes for the app
 *
 * @var $app App
 */
function setupRoutes($app) {

    $app->get('/', 'Web\\PageRenderers\\renderHomePage');

    $app->get('/users', 'getUsers', true);
    $app->get('/users/{userId}', 'getUsers', true);

    $app->get('/createaccount', "Web\\PageRenderers\\renderSignUp");
    $app->post('/createaccount', 'signUp');

    $app->get('/login', 'Web\\PageRenderers\\renderLogIn');
    $app->post('/login', 'logIn');

    $app->get("/logout", "logOut");
    $app->get("/favicon.ico", function(Request $request, $args) {include __DIR__ . '/../../static/images/icons/icons8-new-york-80.png';});

    $app->get("/route/{withVar}/for/testing", function(Request $request, $args) {echo 'IT WORKED';}, true);

    $app->get("/route/{withVar}/for/{testing}/doodoo", function(Request $request, $args) {echo 'IT WORKED';});
}

/* =====================================================================
 *
 * FUNCTIONS ASSOCIATED WITH ROUTES
 *
 * =====================================================================
 */

function getUsers(Request $request, $args) {
   $userIds =  (isset($args['userId'])) ?
            $args['userId'] :
            $request->getQueryParam('userid', []);
    DbApi\getUsersFromDB($userIds);
}

function signUp(Request $request, $args) {
    $email = $_POST["email"];
    $unencryptedPwd = $_POST["password"];
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];

    DbAPI\addUserToDB($email, $unencryptedPwd, $firstName, $lastName);
}


function logIn(Request $request, $args) {
    $email = $_POST["email"];
    $unencryptedPwd = $_POST["password"];

    DbAPI\logInUser($email, $unencryptedPwd);
}



function logOut(Request $request, $args) {
    // destroy the session, send back to home
    // https://www.w3schools.com/php/php_sessions.asp
    session_unset();
    session_destroy();
    header('Location: /');
}



/* =====================================================================
 *
 * HELPERS
 *
 * =====================================================================
 */