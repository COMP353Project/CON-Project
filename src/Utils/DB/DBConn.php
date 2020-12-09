<?php

namespace Utils\DB;

require_once(__DIR__ . "/DbAccessColumns.php");

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
        return $this->queryWithValues($sql, []);
    }

    public function queryWithValues(string $sql, array $values, array $oneOffMap = []) {
        /* @var $statement PDOStatement */
        $statement = $this->conn->prepare($sql);
        $statement->execute($values);
        $initialResult = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map(
            function ($row) use ($oneOffMap) {
                $mappedRow = [];
                foreach ($row as $key => $value) {
                    // rename the column
                    $map = (isset($oneOffMap[$key])) ? $oneOffMap : DbAccessColumns::columnMapping;
                    // cast if necessary
                    if ($map[$key]["type"] != "string") {
                        // DO THE CAST
                        // TODO deal with NULL probably
                        if (!is_null($value)) {
                            settype($value, $map[$key]["type"]);
                        }
                    }
                    $key = (isset($map[$key]['name'])) ? $map[$key]['name'] : $key;
                    $mappedRow[$key] = $value;
                }
                return $mappedRow;
            },
            $initialResult
        );
    }
    
    public function close() {
        $this->conn = null;
    }
}
