<?php
include_once(__DIR__ . "/classes/utils/DB.php");
include_once(__DIR__ . "/classes/utils/DBConn.php");

$db = DB::getInstance();

$sql = <<<EOD
SHOW tables;
EOD;

/* @var $dbConn \classes\utils\DBConn */
try {

    $dbConn = $db->getConnection();
    /* @var $statement PDOStatement */
    $statement = $dbConn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $json = json_encode($results);
    echo print_r($json);

} catch (Exception $e) {
    echo "RUH ROH";
}
