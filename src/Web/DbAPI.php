<?php


namespace Web\DbAPI;

include_once(__DIR__ . "/../Utils/DB.php");
include_once(__DIR__ . "/../Utils/DBConn.php");


use Utils\DB;
use Utils\DBConn;


function getUsers($requestParams) {
    echo "\n\n";
    echo "REQUEST HANDLED BY GETUSERS";
}


function signUp($requestParams) {

    $email = $_POST["email"];
    $unencryptedPwd = $_POST["password"];
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];

    $checkUserExistsSql = 'SELECT EXISTS(select email from users where email = :email) as "exists";';

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($checkUserExistsSql, [":email" => $email]);
    if ($res[0]["exists"] == "1") {
        // user already exists
        // .... redirect somewhere?
        echo "ERROR: USER ALREADY EXISTS";
    } else {
        $hashedPwd = password_hash($unencryptedPwd, PASSWORD_DEFAULT);
        $createUserSQL = "INSERT INTO users (email, firstname, lastname, password) VALUES (:email, :firstname, :lastname, :password);";
        $result = $dbConn->queryWithValues($createUserSQL, [
            ":email" => $email,
            ":firstname" => $firstName,
            ":lastname" => $lastName,
            ":password" => $hashedPwd
        ]);
    }
}