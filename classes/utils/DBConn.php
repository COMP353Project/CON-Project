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
            "dsn" => getenv("DATABASE_URL"),
            "user" => getenv("DATABASE_USERNAME"),
            "pwd" => getenv("DATABASE_PWD")
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
