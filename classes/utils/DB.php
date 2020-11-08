<?php

namespace classes\utils;


class DB {
    public static $instance;
    protected $pool;
    private $autoCommit;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct(bool $autoCommit = false) {
        $this->pool = [];
        $this->autoCommit = $autoCommit;
    }

    public function __destruct() {
        foreach ($this->pool as $conn) {
            $conn->close();
        }
    }

    /**
     * @param string $name
     * @param string|null $dsn
     * @return mixed
     * @throws \Exception
     */
    public function getConnection(string $name = 'default', string $dsn = null) {
        if (!isset($this->pool[$name])) {
            $this->pool[$name] = new DBConn($dsn, $this->autoCommit);
        }
        return $this->pool[$name];
    }

    /**
     * @param string $name
     * @param string|null $dsn
     * @return mixed
     * @throws \Exception
     */
    public static function conn(string $name = 'default', string $dsn = null) {
        return self::getInstance()->getConnection($name, $dsn);
    }

    public static function setAutoCommit(bool $autoCommit = false) {
        $that = self::getInstance();
        $that->autoCommit = $autoCommit;
        foreach ($that->pool as $conn) {
            $conn->setAutoCommit($autoCommit);
        }
    }
}