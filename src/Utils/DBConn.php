<?php

namespace Utils;

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

        $this->conn = new PDO($this->dbConnectionParams['dsn'], $this->dbConnectionParams['user'], $this->dbConnectionParams['pwd'], [PDO::ERRMODE_EXCEPTION]);

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
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryWithValues(string $sql, array $values) {
        /* @var $statement PDOStatement */
        $statement = $this->conn->prepare($sql);
        $statement->execute($values);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function close() {
        $this->connection = null;
    }
}
