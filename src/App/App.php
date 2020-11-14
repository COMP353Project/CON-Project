<?php

namespace App;

include_once(__DIR__ . "/../Web/DbAPI.php");
include_once(__DIR__ . "/../Http/Request.php");

use Web\DbAPI;
use Http\Request;
use Exception;


class App {

    protected $allRoutes;
    protected $getRoutes;
    protected $postRoutes;
    protected $deleteRoutes;
    protected $putRoutes;
    protected $routesForVerb;
    protected $verb;


    public function __construct() {
        // define containers for routes
        $this->allRoutes = [];
        $this->getRoutes = [];
        $this->postRoutes = [];
        $this->deleteRoutes = [];
        $this->putRoutes = [];
        $this->routesForVerb = null;
        $this->verb = null;
    }

    private function registerRoute(array &$routeArray, string $route, $servicingFunction) {
        $routeArray[$route] = $servicingFunction;
        $this->allRoutes[] = $route;
    }

    /**
     * @param string $route
     * @param callable $servicingFunction
     */
    public function get(string $route, $servicingFunction) {
        // method to register a get route
        $this->registerRoute($this->getRoutes, $route, $servicingFunction);
    }

    /**
     * @param string $route
     * @param callable $servicingFunction
     */
    public function post(string $route, $servicingFunction) {
        $this->registerRoute($this->postRoutes, $route, $servicingFunction);
    }

    public function run() {
        try {
            $request = new Request();
            session_start();
            // service the request
            $this->service($request);
        } catch (Exception $e) {
            http_response_code(500);
            echo "Internal Server Error.";
        }
    }

    private function service(Request $request) {
        // decide which routes to look in
        $this->verb = $request->getMethod();
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
                // throw exception with HTTP error code if not implemented
                break;
        }
        // get the route
        // get the function associated with the route
        [$route, $parsedArgs] =  $request->extractEndPoint($this->allRoutes);
        $servicingFunctionName = $this->routesForVerb[$route];
        // TODO MAYBE create Response class
        //      with those created, instantiate one of each here, and pass to servicingFunction
        // call the servicingFunction
        call_user_func_array($servicingFunctionName, [$request, $parsedArgs]);

        if ($this->verb == "POST") {
            unset($_POST);
        }
    }
}