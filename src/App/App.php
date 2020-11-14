<?php

namespace App;

include_once(__DIR__ . "/../Web/DbAPI.php");
include_once(__DIR__ . "/../Http/Request.php");
require_once(__DIR__ . "/../Http/ConHttpException.php");

use Web\DbAPI;
use Http\Request;
use Exception;
use Http\ConHttpException;


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

    private function registerRoute(array &$routeArray, string $route, $servicingFunction, $requiresLogin = false) {
        $routeArray[$route] = [
            'function' => $servicingFunction,
            'requiresLogin' => $requiresLogin
        ];
        $this->allRoutes[] = $route;
    }

    /**
     * @param string $route
     * @param callable $servicingFunction
     * @param bool $requiresLogin
     */
    public function get(string $route, $servicingFunction, $requiresLogin = false) {
        // method to register a get route
        $this->registerRoute($this->getRoutes, $route, $servicingFunction, $requiresLogin);
    }

    /**
     * @param string $route
     * @param callable $servicingFunction
     * @param bool $requiresLogin
     */
    public function post(string $route, $servicingFunction, $requiresLogin = false) {
        $this->registerRoute($this->postRoutes, $route, $servicingFunction, $requiresLogin);
    }

    public function run() {
        try {
            $request = new Request();
            session_start();
            // service the request
            $this->service($request);
        } catch (ConHttpException $e) {
            http_response_code($e->getStatusCode());
            echo $e->getMessage();
        } catch (Exception $e) {
            // something else bad happened
            http_response_code(500);
            echo "An error occurred processing the request.";
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
                throw new ConHttpException("Method not implemented", 405);
                break;
        }
        // get the route
        // get the function associated with the route
        [$route, $parsedArgs] =  $request->extractEndPoint($this->allRoutes);

        // TODO MAYBE create Response class
        //      with those created, instantiate one of each here, and pass to servicingFunction

        // check if login required
        if (!$this->routesForVerb[$route]['requiresLogin'] || !empty($_SESSION['userId'])) {
            // user is logged in
            // call the servicingFunction
            $servicingFunctionName = $this->routesForVerb[$route]['function'];
            call_user_func_array($servicingFunctionName, [$request, $parsedArgs]);
        } else {
            // user is not logged in
            // redirect to login and store route to redirect after login
            $_SESSION['urlAfterLogin'] = $request->getRoute();
            header("Location: /login");
        }

        if ($this->verb == "POST") {
            unset($_POST);
        }
    }
}