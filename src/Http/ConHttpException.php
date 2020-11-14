<?php

namespace Http;

use Exception;

class ConHttpException extends Exception {

    private $httpStatusCode;

    public function __construct($message, $code) {
        parent::__construct($message);

        $this->httpStatusCode = $code;
    }

    public function getStatusCode() {
        return $this->httpStatusCode;
    }
}
