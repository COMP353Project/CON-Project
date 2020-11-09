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
            "dsn" => "mysql:host=izc353.encs.concordia.ca;port=3306;dbname=izc353_2",
            "user" => "izc353_2",
            "pwd" => "BYnAgh"
        ];
    }

    public function query(string $sql) {
        // for future reference:https://stackoverflow.com/questions/2770273/pdostatement-to-json
        return $this->conn->query($sql);
    }

    public function prepare(string $sql) {
	return $this->conn->prepare($sql);
    }
    
    public function close() {
        $this->connection = null;
    }
}
