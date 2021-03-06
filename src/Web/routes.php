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
    $app->get('/users/{userId}/connections', 'getUserConnections', true);
    $app->get('/users/{userId}/posts/allvisible', 'getPostsVisibleToUser', true);
    $app->get('/users/{userId}/emails/sent', "getSentEmails", true);
    $app->get('/users/{userId}/emails/received', "getReceivedEmails", true);
    $app->get('/users/{userId}/associations/administered', 'getUserAdministeredAssociations', true);


    $app->get('/groups/{id}', 'renderGroupPage', true);
    $app->get('/groups/search/groupnames', 'getGroupNames');
    $app->get('/groups/search/byid', 'getGroupsById', true);
    $app->post('/groups/add/byname', 'createNewGroup', true);
    $app->get("/groups/{groupId}/posts", "getGroupPosts", true);
    $app->get("/groups/{groupId}/members/potential", "getPotentialMembers", true);
    $app->get("/groups/{groupId}/members", "getCurrentGroupMembers", true);
    $app->post("/groups/{groupId}/administer", "administerUsersForGroup", true);
    $app->post("/groups/{groupId}/removeusers", "removeUsersFromGroup", true);

    $app->get('/condos', 'getCondos', true);

    $app->get("/association/{associationId}", "renderAssociationPage", true);
    $app->get("/association/{associationId}/posts", "getAssociationPosts", true);
    $app->get('/association/get/byid', 'getAssociationsById', true);
    $app->post("/association/{associationId}/administer", "administerUsersForAssociation", true);
    $app->get("/association/{associationId}/members", "getCurrentAssociationMembers", true);

    $app->get("/posts/{postId}", "getPost", true);
    $app->get("/posts/{postId}/comments", "getPostComments", true);
    $app->get("/posts/{postId}/conversation", "renderPostPage", true);
    $app->post("/posts/create/post", "createPost", true);
    $app->post('/posts/create/comment', "createComment", true);

    $app->get("/buildings", "getBuildings", true);

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
    $app->get('/js/{javaScript}', 'renderJavaScript');

    $app->get("/route/{withVar}/for/testing", function(Request $request, $args) {echo 'IT WORKED';}, true);

    $app->get("/route/{withVar}/for/{testing}/doodoo", function(Request $request, $args) {echo 'IT WORKED';});

    //$app->post('/posts/create/comment', "createComment", true);
    $app->get('/email', 'renderEmailPage', true);
    $app->post('/email', 'sendEmail', true);
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
    $res = DbApi\getUsersFromDB($userIds, !is_null($request->getQueryParam('extrainfo')));
    header('Content-type: application/json');
    echo json_encode($res);
}

function getUserAdministeredAssociations(Request $req, $args) {
    $res = DbAPI\getUserAdministeredAssociationsFromDB($args['userId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getUserConnections(Request $req, $args) {
    $res = DbAPI\getUserConnections($args['userId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getPostsVisibleToUser(Request $req, $args) {
    $res = DbAPI\getPosts($args['userId'], null, null);
    header('Content-type: application/json');
    echo json_encode($res);
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

function renderJavaScript(Request $req, $args) {
    renderStatic("/../../static/js/", $args['javaScript'], 'application/', 'javascript');
}

function renderStatic($path, $name, $baseType, $overrideExtension = null) {
    $extensionType = (is_null($overrideExtension)) ? explode(".", $name)[1] : $overrideExtension;
    header_remove("Content-Length");
    header("Content-type: {$baseType}" . $extensionType);
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

function renderPostPage(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, 'postPage');
}

function renderAssociationPage(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, 'associationPage');
}

function bulkInsertUsers(Request $req, $args) {
    $newUserList = $req->getPostBodyKey('newUsers');
    DbAPI\bulkAddUsersToDb($newUserList);
}

function queryForAtAGlance(Request $req, $args) {
    $res = DbAPI\yoyParticipation($args['type']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getGroupNames(Request $req, $args) {
    $res = DbAPI\getGroupNames();
    header('Content-type: application/json');
    echo json_encode($res);
}

function createNewGroup(Request $req, $args) {
    DbAPI\createNewGroup($req->getPostBodyKey('name'), $req->getPostBodyKey('description'));
}

function getGroupsById(Request $req, $args) {
    if ($req->getQueryParam('checksuperuser', 'false') == 'true') {
        $res = DbAPI\getGroupsById(true);
    } else {
        $res = DbAPI\getGroupsById();
    }

    header('Content-type: application/json');
    echo json_encode($res);
}

function getAssociationsById(Request $req, $args) {
    $res = DbAPI\getAssociationsById(!is_null($req->getQueryParam('extrainfo')));
    header('Content-type: application/json');
    echo json_encode($res);
}


function getGroupPosts(Request $req, $args) {
    $res = DbAPI\getPosts(null, $args['groupId'], null);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getAssociationPosts(Request $req, $args) {
    $res = DbAPI\getPosts(null, null, $args['associationId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getPost(Request $req, $args) {
    $res = DbAPI\getPostFromDB($args['postId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getPostComments(Request $req, $args) {
    $res = DbAPI\getPostCommentsFromDB($args['postId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function createPost(Request $req, $args) {
    $res = DbAPI\addPostToDB(
        $req->getPostBodyKey("groupId"),
        $req->getPostBodyKey("contents"),
        $req->getPostBodyKey("isCommentable")
    );
    header('Content-type: application/json');
    echo json_encode($res);
}

function createComment(Request $req, $args) {
    $res = DbAPI\addCommentToDB(
        $req->getPostBodyKey("postId"),
        $req->getPostBodyKey("message")
    );
    header('Content-type: application/json');
    echo json_encode($res);
}


function getPotentialMembers(Request $req, $args) {
    $res = DbAPI\getPotentialMembers($_SESSION['userId'], $args['groupId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getCurrentGroupMembers(Request $req, $args) {
    $res = DbAPI\getCurrentMembersFromDB($_SESSION['userId'], $args['groupId'], "group_membership");
    header('Content-type: application/json');
    echo json_encode($res);
}

function getCurrentAssociationMembers(Request $req, $args) {
    $res = DbAPI\getCurrentMembersFromDB($_SESSION['userId'], $args['associationId'], "user_roles");
    header('Content-type: application/json');
    echo json_encode($res);
}

function administerUsersForAssociation(Request $req, $args) {
    $res = DbAPI\updateUserRoleInDB(
        $args['associationId'],
        $req->getPostBodyKey('role'),
        $req->getPostBodyKey('userIds'),
        "user_roles"
    );
    header('Content-type: application/json');
    echo json_encode($res);
}

function administerUsersForGroup(Request $req, $args) {
    $res = DbAPI\updateUserRoleInDB(
        $args['groupId'],
        $req->getPostBodyKey('role'),
        $req->getPostBodyKey('userIds'),
        "group_membership"
    );
    header('Content-type: application/json');
    echo json_encode($res);
}

function removeUsersFromGroup(Request $req, $args) {
    $res = DbAPI\removeUsersFromGroupInDB(
        $args['groupId'],
        $req->getPostBodyKey('role'),
        $req->getPostBodyKey('userIds'),
        "group_membership"
    );
    header('Content-type: application/json');
    echo json_encode($res);
}


function getCondos(Request $req, $args) {
    $res = DbAPI\getCondosFromDB($req->getQueryParam('associationid'));
    header('Content-type: application/json');
    echo json_encode($res);
}


function renderEmailPage(Request $req, $args) {
    PageRenderer::renderPageForWeb($req, $args, 'emailPage');
}

function getSentEmails(Request $req, $args) {
    $res = DbAPI\getSentEmailsFromDB($args['userId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function getReceivedEmails(Request $req, $args) {
    $res = DbAPI\getReceivedEmailsFromDB($args['userId']);
    header('Content-type: application/json');
    echo json_encode($res);
}

function sendEmail(Request $req, $args) {
    DbAPI\sendEmailInDB(
        $req->getPostBodyKey('subject'),
        $req->getPostBodyKey('content'),
        $req->getPostBodyKey('recipients')
    );
}


function getBuildings(Request $req, $args) {
    $res = DbAPI\getBuildingsFromDB($req->getQueryParam('associationid'));
    header('Content-type: application/json');
    echo json_encode($res);
}

/* =====================================================================
 *
 * HELPERS
 *
 * =====================================================================
 */