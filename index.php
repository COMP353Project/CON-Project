<?php
include_once(__DIR__ . '/src/app/App.php');
include_once(__DIR__ . '/src/web/routes.php');

use utils\DB;
use app\App;
use web\dbAPI;


$db = DB::getInstance();

$sql = <<<EOD
SHOW tables;
EOD;

/* @var $dbConn utils\DBConn */
try {

    $dbConn = $db->getConnection();
    echo $dbConn->query($sql);

} catch (Exception $e) {
    echo "RUH ROH";
    echo "\n\nEXCEPTION:\n" . $e->getMessage() . "\n\n\n";

    var_dump($e);
}

// create the app
$app = new App();
// register all routes
setupRoutes($app);
// run the app to service the request
$app->run();