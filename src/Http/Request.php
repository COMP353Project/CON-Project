<?php

namespace Http;
use Exception;
use Http\ConHttpException;

class Request {

    private $method;
    private $body;
    private $headers;
    private $route;
    private $queryParams;
    private $queryString;

    public function __construct() {

        $this->queryParams = [];
        $this->headers = [];
        $this->body = null;

        $this->method = $_SERVER['REQUEST_METHOD'];
        if (strpos($_SERVER['REQUEST_URI'], '?')) {
            [$this->route, $this->queryString] = explode('?', $_SERVER['REQUEST_URI']);
        } else {
            $this->route = $_SERVER['REQUEST_URI'];
            $this->queryString = null;
        }
        $this->extractHeaders();
        if (!is_null($this->queryString)) {
            $this->extractQueryParams();
        }

        // if POST, PARSE BODY
        if ($this->method == "POST") {
            if ($this->headers["CONTENT_TYPE"] == "application/json") {
                // json is easy, set body to be json decoded input
                $inputJSON = file_get_contents('php://input');
                $this->body = json_decode($inputJSON, true);
            } elseif (
                $this->headers["CONTENT_TYPE"] == "application/x-www-form-urlencoded" ||
                strpos($this->headers["CONTENT_TYPE"], "multipart/form-data;")
            ) {
                // post is form data, go through it
                $this->body = [];
                foreach($_POST as $key => $value) {
                    $this->body[$key] = $value;
                }
            }
        }
    }

    public function getMethod() {
        return $this->method;
    }

    public function getRoute() {
        return $this->route;
    }

    private function extractQueryParams() {
        foreach (explode("&", $this->queryString) as $item) {
            [$param, $value] = explode("=", $item);
            $paramIsList = false;
            // see if its a list param
            if (substr($param, strlen($param) - 2) == "[]") {
                $param = substr_replace($param, "", -2);
                $paramIsList = true;
            }
            // add an entry
            if (!isset($this->queryParams[$param])) {
                if ($paramIsList) {
                    $this->queryParams[$param] = [];
                } else {
                    $this->queryParams[$param] = null;
                }
            }
            // set the value
            if ($paramIsList) {
                $this->queryParams[$param][] = $value;
            } else {
                $this->queryParams[$param] = $value;
            }
        }
    }

    private function parser($currentIdx, $currentPotentialRouteParts, $requestedRouteParts, $parsedArgs): array {
        if ($currentIdx == sizeof($currentPotentialRouteParts)) {
            return [true, $parsedArgs];
        } elseif (
            substr($currentPotentialRouteParts[$currentIdx], 0, 1) == "{" &&
            substr($currentPotentialRouteParts[$currentIdx], -1) == "}"
        ) {
            $parsedArgs[substr($currentPotentialRouteParts[$currentIdx], 1, strlen($currentPotentialRouteParts[$currentIdx]) - 2)] = $requestedRouteParts[$currentIdx];
            return $this->parser($currentIdx + 1, $currentPotentialRouteParts, $requestedRouteParts, $parsedArgs);
        } elseif ($requestedRouteParts[$currentIdx] == $currentPotentialRouteParts[$currentIdx]) {
            return $this->parser($currentIdx + 1, $currentPotentialRouteParts, $requestedRouteParts, $parsedArgs);
        } else {
            return [false, null];
        }
    }

    public function extractEndPoint(array $routeList): array {
        // decls
        $getRemainingParts = function($routeString) {
            return ($routeString == "") ? [] : explode("/", $routeString);
        };

        $parsedArgs = null;
        $requestedEndpoint = null;
        // massage
        $routeParts = array_merge(["/"], $getRemainingParts(ltrim($this->route, "/")));
        foreach ($routeList as $potentialRoute) {
            // massage potential route
            $potentialRouteParts = array_merge(["/"], $getRemainingParts(ltrim($potentialRoute, "/")));
            // see if it matches
            if (sizeof($potentialRouteParts) != sizeof($routeParts)) {
                // match not possible if routes differ in size
                continue;
            }
            // recursively check for match
            [$match, $parsedArgs] = $this->parser(0, $potentialRouteParts, $routeParts, []);
            if ($match) {
                // found matching route
                $requestedEndpoint = $potentialRoute;
                break;
            }
        }
        if (!is_null($requestedEndpoint)) {
            return [$requestedEndpoint, $parsedArgs];
        } else {
            throw new ConHttpException("Route does not exist", 404);
        }
    }

    private function extractHeaders() {
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, "HTTP_") !== false) {
                $this->headers[substr($key, 5)] = $value;
            } elseif (in_array($key, ["CONTENT_TYPE", "CONTENT_LENGTH"])) {
                $this->headers[$key] = $value;
            }
        }
    }

    public function getQueryParam($paramName, $default = null) {
        if (isset($this->queryParams[$paramName])) {
            return $this->queryParams[$paramName];
        } else {
            return $default;
        }
    }

    public function getPostBodyKey($key) {
        return $this->body[$key];
    }
}
