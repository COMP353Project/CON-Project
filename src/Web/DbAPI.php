<?php


namespace Web\DbAPI;

include_once(__DIR__ . "/../Utils/DB.php");
include_once(__DIR__ . "/../Utils/DBConn.php");


use Utils\DB;
use Utils\DBConn;
use Http\Request;

/* =====================================================================
 *
 * FUNCTIONS ASSOCIATED WITH ROUTES
 *
 * =====================================================================
 */


function getUsers(Request $request, $args) {
    echo "\n\n";
    echo "REQUEST HANDLED BY GETUSERS";
}

function signUp(Request $request, $args) {

    $email = $_POST["email"];
    $unencryptedPwd = $_POST["password"];
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];

    if (checkUserExists($email)) {
        // user already exists
        // .... redirect somewhere?
        echo "ERROR: USER ALREADY EXISTS";
    } else {
        $hashedPwd = password_hash($unencryptedPwd, PASSWORD_DEFAULT);
        $createUserSQL = "INSERT INTO users (email, firstname, lastname, password) VALUES (:email, :firstname, :lastname, :password);";
        $dbConn = DB::getInstance()->getConnection();
        $result = $dbConn->queryWithValues($createUserSQL, [
            ":email" => $email,
            ":firstname" => $firstName,
            ":lastname" => $lastName,
            ":password" => $hashedPwd
        ]);
        // On success, redirect back to home (FOR NOW)
        header("Location: /");
    }
}

function logIn(Request $request, $args) {

    $email = $_POST["email"];
    $unencryptedPwd = $_POST["password"];

    if (checkUserExists($email)) {
        // user exists, verify login
        $userEmailSQL = "SELECT id, password from users where email = :email;";
        $dbConn = DB::getInstance()->getConnection();
        $res = $dbConn->queryWithValues($userEmailSQL, [":email" => $email]);
        if (password_verify($unencryptedPwd, $res[0]["password"])) {
            // password matches

            // check if hashing algo was changed/ if password needs to be rehashed as per recommendation here:
            // https://alexwebdevelop.com/php-password-hashing/
            if (password_needs_rehash($res[0]["password"], PASSWORD_DEFAULT)) {
                $newHashedPwd = password_hash($unencryptedPwd, PASSWORD_DEFAULT);
                $updateHashedPwdSQL = "UPDATE users SET password = :password where email = :email;";
                $dbConn->queryWithValues($updateHashedPwdSQL, [":password" => $newHashedPwd, ":email" => $email]);
            } else {
                // START A SESSION
                // https://www.w3schools.com/php/php_sessions.asp
                // send user back to home
                $_SESSION["userId"] = $res[0]["id"];

                header("Location: /");
            }
        } else {
            // invalid password, send back to login
            header("Location: /login");
        }
    } else {
        // User does not exist, redirect to create account (FOR NOW)
        header("Location: /createaccount");
    }

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


function checkUserExists(string $email) {
    $checkUserExistsSql = 'SELECT EXISTS(select email from users where email = :email) as "exists";';

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($checkUserExistsSql, [":email" => $email]);
    return $res[0]["exists"] == "1";
}