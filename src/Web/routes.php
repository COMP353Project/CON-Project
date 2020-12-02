<?php

include_once(__DIR__ . "/../App/App.php");
include_once(__DIR__ . "/../Web/DbAPI.php");
include_once(__DIR__ . "/../Web/PageRenderer.php");
include_once(__DIR__ . "/../Http/Request.php");

use App\App;
use Http\Request;
use Web\DbAPI;
use Web\PageRenderer;

/**
 * Register all the routes for the app
 *
 * @var $app App
 */
function setupRoutes($app) {

    $app->get('/', 'renderHomePage');

    $app->get('/profile', 'renderProfile', true);
    $app->get('/administer', 'renderAdminister', true);

    $app->get('/users', 'getUsers', true);
    $app->get('/users/{userId}', 'getUsers', true);
    $app->post('/users/bulk/create', 'bulkInsertUsers');

    $app->get('/groups/{id}', 'renderGroupPage', true);
    $app->get('/groups/search/groupnames', 'getGroupNames', true);
    $app->get('/groups/search/byid', 'getGroupsById', true);
    $app->post('/groups/add/byname', 'createNewGroup', true);

    $app->get('/permissions/loggedinuserperms', 'getLoggedInUserPerms');

    $app->get('/createaccount', "renderSignUp");
    $app->post('/createaccount', 'signUp');

    $app->get('/ataglance', 'renderAbout');
    $app->get('/ataglance/yearoveryear/{type}', 'queryForAtAGlance');

    $app->get('/login', 'renderLogIn');
    $app->post('/login', 'logIn');

    $app->get("/logout", "logOut");
    $app->get("/favicon.ico", function(Request $request, $args) {renderIcon($request, ["iconName" => "icons8-new-york-80.png"]);});
    $app->get("/images/{imageName}", "renderImage");
    $app->get("/icons2/{iconName}", "renderIcon");
    $app->get("/css/{styleSheet}", "renderStylesheet");

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
    $email = $request->getPostBodyKey("cEmail");
    $unencryptedPwd = $request->getPostBodyKey("cPassword");
    $firstName = $request->getPostBodyKey("firstName");
    $lastName = $request->getPostBodyKey("lastName");

    DbAPI\addUserToDB($email, $unencryptedPwd, $firstName, $lastName);
}


function logIn(Request $request, $args) {
    $email = $request->getPostBodyKey("email");
    $unencryptedPwd = $request->getPostBodyKey("password");

    DbAPI\logInUser($email, $unencryptedPwd);
}

function renderImage(Request $request, $args) {
    renderStatic("/../../static/images/pics/", $args['imageName'], 'image/');
}

function renderIcon(Request $request, $args) {
    renderStatic("/../../static/images/icons/", $args['iconName'], 'image/');
}

function renderStylesheet(Request $request, $args) {
    renderStatic("/../../static/css/", $args['styleSheet'], 'text/');
}

function renderStatic($path, $name, $baseType) {
    header_remove("Content-Length");
    header("Content-type: {$baseType}" . explode(".", $name)[1]);
    include  __DIR__ . $path . $name;
}



function logOut(Request $request, $args) {
    // destroy the session, send back to home
    // https://www.w3schools.com/php/php_sessions.asp
    session_unset();
    session_destroy();
    header('Location: /');
}

function getLoggedInUserPerms(Request $req, $args) {
    header('Content-Type: application/json');
    $resp = ['loggedIn' => false, 'hasCreatePermission' => false];

    if (isset($_SESSION['userId'])) {
        $resp = DbAPI\checkUserHasCreatePermission($resp, $_SESSION['userId']);
    }
    echo json_encode($resp);
}

function renderSignUp(Request $req, $args) {
    PageRenderer::renderLogIn($req, $args);
}

function renderHomePage(Request $req, $args) {
    PageRenderer::renderHomePage($req, $args);
}

function renderLogIn(Request $req, $args) {
    PageRenderer::renderLogIn($req, $args);
}

function renderAbout(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, "aboutPage");
}

function renderProfile(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, 'profilePage');
}

function renderAdminister(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, 'administerPage');
}

function renderGroupPage(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, 'groupPage');
}

function bulkInsertUsers(Request $req, $args) {
    $newUserList = $req->getPostBodyKey('newUsers');
    DbAPI\bulkAddUsersToDb($newUserList);
}

function queryForAtAGlance(Request $req, $args) {
    DbAPI\yoyParticipation($args['type']);
}

function getGroupNames(Request $req, $args) {
    DbAPI\getGroupNames();
}

function createNewGroup(Request $req, $args) {
    DbAPI\createNewGroup($req->getPostBodyKey('name'), $req->getPostBodyKey('description'));
}

function getGroupsById() {
    DbAPI\getGroupsById();
}

/* =====================================================================
 *
 * HELPERS
 *
 * =====================================================================
 */