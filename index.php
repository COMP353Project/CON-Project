<?php
include_once(__DIR__ . "/classes/utils/DB.php");
include_once(__DIR__ . "/classes/utils/DBConn.php");

$db = classes\utils\DB::getInstance();

$sql = <<<EOD
SHOW tables;
EOD;

phpinfo();

/* @var $dbConn classes\utils\DBConn */
try {

    $dbConn = $db->getConnection();
    echo $dbConn->query($sql);

} catch (Exception $e) {
    echo "RUH ROH";
    echo "\n\nEXCEPTION:\n" . $e->getMessage() . "\n\n\n";

    var_dump($e);
}
