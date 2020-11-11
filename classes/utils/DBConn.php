<?php

namespace classes\utils;

use \Exception;
use \PDO;
use \PDOStatement;


class DBConn {
    // Postgres database connection
    protected $conn;
    protected $dbConnectionParams;

    /**
     * DBConn constructor.
     * @param array|null $dbConnectionParams
     * @throws Exception
     */
    public function __construct(?array $dbConnectionParams) {
        if (is_null($dbConnectionParams)) {
            $this->dbConnectionParams = self::getDefaultConnectionParams(); 
        }

        $this->conn = new PDO($this->dbConnectionParams['dsn'], $this->dbConnectionParams['user'], $this->dbConnectionParams['pwd']);

        if (!$this->conn) {
            throw new Exception('connect Error');
        }
    }

    static private function getDefaultConnectionParams() {
        return [
            "dsn" => "mysql:host=izc353.encs.concordia.ca;port=3306;dbname=izc353_2",
            "user" => "izc353_2",
            "pwd" => "BYnAgh"
        ];
    }

    public function query(string $sql) {
        /* @var $statement PDOStatement */
        $statement = $this->conn->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($results);
    }
    
    public function close() {
        $this->connection = null;
    }
}
