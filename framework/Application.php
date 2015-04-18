<?php namespace Framework;

use FastRoute\simpleDispatcher;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use Illuminate\Container\Container;

class Application 
{

    public $container;

    protected $httpMethod;

    protected $uri;

    protected $scriptName;

    protected $requestUri;

    protected $routeInfo;

    protected $routes;

    protected $dispatcher;

    protected static $controllerDelimiter = '@';

    protected static $controllerNamespace = 'Framework\Controllers';

    public function __construct()
    {
        $this->container = new Container;

        $this->container->singleton('Twig_Environment', function(){
            $loader = new \Twig_Loader_Filesystem(__DIR__.'/views');
            return new \Twig_Environment($loader, ['cache' => __DIR__.'/storage']);
        });

        $this->httpMethod = $_SERVER['REQUEST_METHOD'];
        $this->scriptName = $_SERVER['SCRIPT_NAME'];
        $this->requestUri = $_SERVER['REQUEST_URI'];

        new DatabaseConnection;

        $this->uri = $this->prepareUri($this->requestUri, $this->scriptName);
    }

    public function prepareUri($requestUri, $scriptName)
    {
        if (strpos($requestUri, $scriptName) !== false) {
            $physicalPath = $scriptName; // <-- Without rewriting
        } else {
            $physicalPath = str_replace('\\', '', dirname($scriptName)); // <-- With rewriting
        }

        if (substr($requestUri, 0, strlen($physicalPath)) == $physicalPath) {
            return substr($requestUri, strlen($physicalPath)); // <-- Remove physical path
        }

        return $physicalPath;
    }

    public function addRoute($method, $uri, $handler)
    {
        $this->routes[] = ['method' =>strtoupper($method), 'uri' => $uri, 'handler' => $handler];
        return $this;
    }

    protected function registerRoutesWithDispatcher($routes)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use ($routes) {
            foreach ($routes as $key => $route) 
            {
                $r->addRoute($route['method'], $route['uri'], $route['handler']);
            }
        });
    }

    public function run()
    {
        // set response code
        http_response_code(200);
        header("Content-Type:text/html");
        // dispatch stuff
        $this->registerRoutesWithDispatcher($this->routes);

        $routeInfo = $this->dispatcher->dispatch($this->httpMethod, $this->uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->renderREsponse($this->callHandler($handler, $vars));
                break;
        }
    }

    /**
    * Determines if dispatched handler is a callable or a string.
    * If it's a callable we can run straight away and pass vars.
    * If it's a string we construct our controller and execute the method
    * that is on the controller.
    * @param mixed $handler
    * @param array $vars
    *
    * @return mixed
    **/

    protected function callHandler($handler, $vars)
    {
        // if its an ananomous function lets call it
        if (is_callable($handler))
        {
            return call_user_func_array($handler, $vars);
        }

        // if its a string lets parse it
        if (is_string($handler))
        {
            $arr = explode(self::$controllerDelimiter, $handler);
            $controllerName = $arr[0];
            $methodName = $arr[1];

            // automatically detect if correctly namespaced and if not add it
            if ($this->controllerNamespaceMissing($controllerName)) $controllerName = $this->addNamespaceToController($controllerName);

            // contrcut and run method
            $controller = $this->container->make($controllerName);
            //$controller = new $controllerName($this);

            // call method of controller with vars array
            return call_user_func_array([$controller, $methodName], $vars);
        }

    }

    protected function renderResponse($result)
    {
        // if its a string echo out
        if (is_string($result))
        {
            echo $result;
            return;
        }      

        // else cast to json and return as json
        header("Content-Type:text/json");
        echo json_encode($result);
        return;
    }

    protected function controllerNamespaceMissing($controllerName)
    {
        return (strrpos($controllerName, self::$controllerNamespace) !== 0);
    }

    protected function addNamespaceToController($controllerName)
    {
        return self::$controllerNamespace.'\\'.$controllerName;
    }

}