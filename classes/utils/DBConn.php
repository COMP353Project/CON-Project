<?php

namespace classes\utils;

use \Exception;
use \PDO;

class DBConn {
    // Postgres database connection
    protected $conn;
    protected $dbConnectionParams;
    protected $inTx;
    protected $autoCommit;


    /**
     * DBConn constructor.
     * @param string|null $dsn
     * @param $autoCommit
     * @throws Exception
     */
    public function __construct(?array $dsn, $autoCommit)
    {
        $this->inTx = false;
        $this->autoCommit = $autoCommit;
        if (is_null($dsn)) {
            $this->dbConnectionParams = self::getDefaultConnectionParams();
        }

        $this->conn = new PDO($this->dbConnectionParams['dsn'], $this->dbConnectionParams['user'], $this->dbConnectionParams['pwd']);

        if (!$this->conn) {
            throw new Exception('connect Error');
        }
        $this->dsn = $dsn;

    }

    static private function getDefaultConnectionParams() {
        return [
            "dsn" => getenv("DATABASE_URL"),
            "user" => getenv("DATABASE_USERNAME"),
            "pwd" => getenv("DATABASE_PWD")
        ];
    }

    public function query(string $sql) {
        // for future reference:https://stackoverflow.com/questions/2770273/pdostatement-to-json
        return $this->conn->query($sql);
    }
}
