<?php

namespace Mahdianmanesh\LaravelTestGenerator;

use ReflectionMethod;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use Mahdianmanesh\LaravelTestGenerator\TestCaseGenerator;

class Generator
{
    protected $routeFilter;

    protected $originalUri;

    protected $action;

    protected $config;

    protected $testCaseGenerator;

    protected $formatter;

    protected $directory;

    protected $sync;

    /**
     * Initiate the global parameters
     * 
     * @param string $directory the root directory for all tests
     * @param bool $sync
     * @param mixed $filter
     */
    public function __construct(string $directory = '', bool $sync = false, $filter = null)
    {
        $this->directory   = $directory;
        $this->routeFilter = $filter;
        $this->sync        = $sync;

        $this->testCaseGenerator = new TestCaseGenerator();
        $this->formatter         = new Formatter($directory, $sync);
    }

    /**
     * Generate the route methods and write to the file
     *
     * @return void
     */
    public function generate()
    {
        $this->getRouteMethods();
        $this->formatter->generate();
    }

    /**
     * Get the route detail and generate the test cases
     *
     * @return void
     */
    protected function getRouteMethods()
    {
        foreach ($this->getAppRoutes() as $route) {

            $this->originalUri = $this->getRouteUri($route);
            $uri = $this->strip_optional_char($this->originalUri);

            // exclude routeFilter
            if (
                $this->routeFilter &&
                !preg_match(
                    '/^' . preg_quote($this->routeFilter, '/') . '/',
                    $uri
                )
            ) {
                continue;
            }   

            $action         = $route->getAction('uses');
            $methods        = $route->methods();
            $actionName     = $this->getActionName($route->getActionName());
            $controllerName = $this->getControllerName($route->getActionName());

            foreach ($methods as $method) {

                $method = strtoupper($method);
                
                if (in_array($method, ['HEAD'])) continue;
                
                $rules   = $this->getFormRules($action) ?? [];
                $case    = $this->testCaseGenerator->generate($rules);
                $hasAuth = $this->isAuthorizationExist($route->middleware());

                $this->formatter->format($case, $uri, $method, 
                                        $controllerName, $actionName, $hasAuth);
            }
        }
    }

    /**
     * Check authorization middleware is exist
     *
     * @param array $middlewares
     * @return boolean
     */
    protected function isAuthorizationExist($middlewares) : bool
    {
        return (bool) array_filter(
            $middlewares,
            fn ($var) => strpos($var, 'auth') > -1
        );
    }

    /**
     * Replace the optional params from the URL
     *
     * @param string $uri
     * @return string
     */
    protected function strip_optional_char($uri) : string
    {
        return str_replace('?', '', $uri);
    }

    /**
     * Get the routes of the application
     *
     * @return array
     */
    protected function getAppRoutes() : array
    {
        return app('router')->getRoutes();
    }

    /**
     * Get the URI of the route
     *
     * @param Route $route
     * @return string
     */
    protected function getRouteUri(Route $route) : string
    {
        $uri = $route->uri();

        if (!starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        return $uri;
    }

    /**
     * Get the form rules for creating the parameters
     *
     * @param string|mixed $action
     * @return array
     */
    protected function getFormRules($action) : array
    {
        if (!is_string($action)) return [];
        
        $parsedAction = Str::parseCallback($action);
        
        $reflector = (new ReflectionMethod($parsedAction[0], $parsedAction[1]));
        $parameters = $reflector->getParameters();
        
        foreach ($parameters as $parameter) {
            $class = optional($parameter->getType())->getName();
            
            if (is_subclass_of($class, FormRequest::class)) {
                return (new $class)->rules();
            }
        }
    }

    /**
     * Return's the controller name
     *
     * @param string $controller
     * @return string
     */
    protected function getControllerName($controller)
    {
        $namespaceReplaced   = substr($controller, strrpos($controller, '\\')+1);
        $actionNameReplaced  = substr($namespaceReplaced, 0, strpos($namespaceReplaced, '@'));
        $controllerReplaced  = str_replace('Controller', '', $actionNameReplaced);
        $controllerNameArray = preg_split('/(?=[A-Z])/', $controllerReplaced);
        $controllerName      = trim(implode('', $controllerNameArray));

        return $controllerName;
    }

    /**
     * Return's the action name
     *
     * @param string $actionName
     * @return string
     */
    protected function getActionName($actionName)
    {
        $actionNameSubString = substr($actionName, strpos($actionName, '@')+1);
        $actionNameArray     = preg_split('/(?=[A-Z])/', ucfirst($actionNameSubString));
        $actionName          = trim(implode('', $actionNameArray));

        return $actionName;
    }
}
