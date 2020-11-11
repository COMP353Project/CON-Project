<?php

namespace app;

include_once(__DIR__ . "/../web/dbAPI.php");

use web\dbAPI;


class App {

    protected $getRoutes;
    protected $postRoutes;
    protected $deleteRoutes;
    protected $putRoutes;
    protected $routesForVerb;


    public function __construct() {
        // define containers for routes
        $this->getRoutes = [];
        $this->postRoutes = [];
        $this->deleteRoutes = [];
        $this->putRoutes = [];
        $this->routesForVerb = null;
    }

    /**
     * @param string $route
     * @param callable $responder
     */
    public function get(string $route, $responder) {
        // method to register a get route
        $this->getRoutes[$route] = $responder;
    }

    public function run() {
        // decide which routes to look in
        switch($_SERVER['REQUEST_METHOD']) {
            case "GET":
                $this->routesForVerb = $this->getRoutes;
                break;
            case "POST":
            case "DELETE":
            case "PUT":
            default:
                break;
        }
        // service the request
        $this->service();
    }

    private function service() {
        // get the route
        $routeRequested = $_SERVER['REQUEST_URI'];
        // get the function associated with the route
        $servicingFunctionName = $this->routesForVerb[$routeRequested];
        // call the function
        call_user_func_array($servicingFunctionName, [[]]);
    }
}