<?php

namespace App;

include_once(__DIR__ . "/../Web/DbAPI.php");

use Web\DbAPI;


class App {

    protected $getRoutes;
    protected $postRoutes;
    protected $deleteRoutes;
    protected $putRoutes;
    protected $routesForVerb;
    protected $verb;


    public function __construct() {
        // define containers for routes
        $this->getRoutes = [];
        $this->postRoutes = [];
        $this->deleteRoutes = [];
        $this->putRoutes = [];
        $this->routesForVerb = null;
        $this->verb = null;
    }

    /**
     * @param string $route
     * @param callable $servicingFunction
     */
    public function get(string $route, $servicingFunction) {
        // method to register a get route
        $this->getRoutes[$route] = $servicingFunction;
    }

    /**
     * @param string $route
     * @param callable $servicingFunction
     */
    public function post(string $route, $servicingFunction) {
        $this->postRoutes[$route] = $servicingFunction;
    }

    public function run() {
        // decide which routes to look in
        $this->verb = $_SERVER['REQUEST_METHOD'];
        switch($this->verb) {
            case "GET":
                $this->routesForVerb = $this->getRoutes;
                break;
            case "POST":
                $this->routesForVerb = $this->postRoutes;
                break;
            case "DELETE":
            case "PUT":
            default:
                break;
        }
        // service the request
        $this->service();
    }

    private function service() {
        session_start();
        // get the route
        $routeRequested = $_SERVER['REQUEST_URI'];
        // get the function associated with the route
        $servicingFunctionName = $this->routesForVerb[$routeRequested];
        // TODO MAYBE create Request (would parse route for args, query for params, etc...), Response classes
        //      with those created, instantiate one of each here, and pass to servicingFunction
        // call the servicingFunction
        call_user_func_array($servicingFunctionName, [[]]);

        if ($this->verb == "POST") {
            unset($_POST);
        }
    }
}