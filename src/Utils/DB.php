<?php

namespace Utils;


class DB {
    public static $instance;
    protected $pool;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->pool = [];
    }

    public function __destruct() {
        foreach ($this->pool as $conn) {
            $conn->close();
        }
    }

    /**
     * @param string $name
     * @param array|null $dsn
     * @return mixed
     * @throws \Exception
     */
    public function getConnection(string $name = 'default', ?array $dsn = null) {
        if (!isset($this->pool[$name])) {
            $this->pool[$name] = new DBConn($dsn);
        }
        return $this->pool[$name];
    }

    /**
     * @param string $name
     * @param array|null $dsn
     * @return mixed
     * @throws \Exception
     */
    public static function conn(string $name = 'default', ?array $dsn = null) {
        return self::getInstance()->getConnection($name, $dsn);
    }
}