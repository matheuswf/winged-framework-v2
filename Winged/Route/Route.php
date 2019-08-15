<?php

namespace Winged\Route;

use Winged\App\Request;
use Winged\App\RequestMiddleware;
use Winged\App\Response;
use Winged\App\ResponseMiddleware;
use Winged\Controller\Controller;
use Winged\Date\Date;
use Winged\Http\Session;
use Winged\Utils\DeepClone;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\App\App;

/**
 * Class Route
 *
 * @package Winged\Route
 */
class Route
{

    /**
     * @var $routes Route[]
     */
    protected static $routes = [];

    /**
     * @var $routes Route | callable | null
     */
    protected static $notFound;

    /**
     * @var $routes Route[]
     */
    protected $groups = [];

    /**
     * @var $name string
     */
    protected $name = '';

    protected $http;

    /**
     * @var \Closure | Route
     */
    protected $callable;

    protected $class;

    protected $method;

    protected $vars = [];

    protected $uri;

    protected $parsed_uri = [];

    protected $uri_count;

    protected $status = false;

    protected $failedIn = false;

    protected $statusCode = 200;

    protected $rules = [];

    protected $origins = [];

    protected $createSessionOptions = [];

    protected $errors = [];

    /**
     * @var ResponseMiddleware[]
     */
    protected $responseMiddleware = [];

    /**
     * @var RequestMiddleware[]
     */
    protected $requestMiddleware = [];

    /**
     * @var int
     */
    protected $priority = -1;

    /**
     * @var int
     */
    protected $argPriority = -1;

    /**
     * @var null | Request
     */
    protected $request = null;

    /**
     * @var null | Response
     */
    protected $response = null;

    protected $continue = false;

    /**
     * Route constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->request = new Request();
        $this->response = new Response($this->request);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Route
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * @param mixed $http
     *
     * @return Route
     */
    public function setHttp($http)
    {
        $this->http = $http;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @param mixed $callable
     *
     * @return Route
     */
    public function setCallable($callable)
    {
        if (is_callable($callable)) {
            $this->callable = \Closure::bind($callable, $this, 'Winged\Route\Route');
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     *
     * @return Route
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     *
     * @return Route
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param mixed $vars
     *
     * @return Route
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     *
     * @return Route
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParsedUri()
    {
        return $this->parsed_uri;
    }

    /**
     * @param mixed $parsed_uri
     *
     * @return Route
     */
    public function setParsedUri($parsed_uri)
    {
        $this->parsed_uri = $parsed_uri;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUriCount()
    {
        return $this->uri_count;
    }

    /**
     * @param mixed $uri_count
     *
     * @return Route
     */
    public function setUriCount($uri_count)
    {
        $this->uri_count = $uri_count;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param bool | string $status
     *
     * @return Route
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailedIn()
    {
        return $this->failedIn;
    }

    /**
     * @param int $failedIn
     *
     * @return Route
     */
    public function setFailedIn($failedIn)
    {
        $this->failedIn = $failedIn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param mixed $rules
     *
     * @return Route
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    /**
     * @param mixed $origins
     *
     * @return Route
     */
    public function setOrigins($origins)
    {
        $this->origins = $origins;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateSessionOptions()
    {
        return $this->createSessionOptions;
    }

    /**
     * @param mixed $createSessionOptions
     *
     * @return Route
     */
    public function setCreateSessionOptions($createSessionOptions)
    {
        $this->createSessionOptions = $createSessionOptions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     *
     * @return Route
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return Route
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return int
     */
    public function getArgPriority()
    {
        return $this->argPriority;
    }

    /**
     * @param int $argPriority
     *
     * @return Route
     */
    public function setArgPriority($argPriority)
    {
        $this->argPriority = $argPriority;
        return $this;
    }

    public function addPriority()
    {
        $this->priority++;
    }

    public function addArgPriority()
    {
        $this->argPriority++;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     *
     * @return Route
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return Route[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Route[] $groups
     *
     * @return Route
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponseMiddleware()
    {
        return $this->responseMiddleware;
    }

    /**
     * @param array $responseMiddleware
     *
     * @return Route
     */
    public function setResponseMiddleware($responseMiddleware)
    {
        $this->responseMiddleware = $responseMiddleware;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequestMiddleware()
    {
        return $this->requestMiddleware;
    }

    /**
     * @param array $requestMiddleware
     *
     * @return Route
     */
    public function setRequestMiddleware($requestMiddleware)
    {
        $this->requestMiddleware = $requestMiddleware;
        return $this;
    }

    /**
     * @param $middleware RequestMiddleware
     *
     * @return $this
     */
    public function addRequestMiddleware($middleware)
    {
        if (get_class($middleware) === 'Winged\App\RequestMiddleware') {
            $this->requestMiddleware[] = $middleware;
        }
        return $this;
    }

    /**
     * @param $middleware ResponseMiddleware
     *
     * @return $this
     */
    public function addResponseMiddleware($middleware)
    {
        if (get_class($middleware) === 'Winged\App\ResponseMiddleware') {
            $this->responseMiddleware[] = $middleware;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getContinue()
    {
        return $this->continue;
    }

    /**
     * @param bool $continue
     *
     * @return Route
     */
    public function setContinue($continue)
    {
        $this->continue = $continue;
        return $this;
    }

    /**
     * jump to next route in filtered routes
     */
    public function nextRoute()
    {
        $this->continue = true;
    }


    /**
     * @return $this
     */
    public function forceJson()
    {
        $this->request()->accept = ['application/json' => 'application/json'];
        return $this;
    }

    /**
     * @return $this
     */
    public function forceHtml()
    {
        $this->request()->accept = ['text/html' => 'application/json'];
        return $this;
    }

    /**
     * @return $this
     */
    public function forceXml()
    {
        $this->request()->accept = ['application/json' => 'application/json'];
        return $this;
    }

    /**
     * @return $this
     */
    public function forceYaml()
    {
        $this->request()->accept = ['application/json' => 'application/json'];
        return $this;
    }


    /**
     * check if an error occured in route
     *
     * @return bool
     */
    protected function checkErrors()
    {
        if ($this->failedIn || !empty($this->errors)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $search
     * @param string $new_name
     *
     * @return mixed|Route
     */
    public static function duplicate($search = '', $new_name = '')
    {
        if (array_key_exists($search, Route::$routes) && $new_name != $search && !array_key_exists($new_name, Route::$routes)) {
            Route::$routes[$new_name] = &DeepClone::factory(Route::$routes[$search])->copy();
            return Route::$routes[$new_name];
        }
        return false;
    }

    /**
     * @return $this
     */
    public function changeToGet()
    {
        $this->method = 'get';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToPost()
    {
        $this->method = 'post';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToPut()
    {
        $this->method = 'put';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToDelete()
    {
        $this->method = 'delete';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToPatch()
    {
        $this->method = 'patch';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToOptions()
    {
        $this->method = 'options';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToRaw()
    {
        $this->method = 'raw';
        return $this;
    }

    /**
     * @param array $origins
     *
     * @return $this
     */
    public function origins($origins = [])
    {
        if (is_array($origins)) {
            $this->setOrigins($origins);
        }
        return $this;
    }

    /**
     * @param $name
     */
    public function name($name)
    {
        if (is_string($name)) {
            Route::$routes[$name] = &Route::$routes[$this->name];
            unset(Route::$routes[$this->name]);
            $this->name = $name;
        }
    }

    /**
     * if use this method, basci auth is required in request for this route
     *
     * @param string | callable $user
     * @param string            $password
     * @param bool              $require_password
     *
     * @return $this
     */
    public function credentials($user = 'root', $password = '', $require_password = false)
    {
        $current = false;
        if (server('php_auth_user') && server('php_auth_pw') || (!$require_password && server('php_auth_user'))) {
            if (is_string($user)) {
                if (!$require_password) {
                    if (!server('php_auth_user') === $user) {
                        $current = true;
                    }
                } else if (!server('php_auth_user') === $user || !server('php_auth_pw') === $password) {
                    $current = true;
                }
            } else if (is_callable($user)) {
                $current = call_user_func_array($user, [server('php_auth_user'), server('php_auth_pw')]);
                if ($current) {
                    if (is_array($current)) {
                        $this->setVars(array_merge($this->getVars(), $current));
                    }
                    $current = false;
                } else {
                    $current = true;
                }
            } else {
                $current = true;
            }
        } else {
            $current = true;
        }
        if ($current) {
            $this->setFailedIn(401);
            $this->setStatus('This request was not authorized by the server. Credentials available in the header are incorrect or not found.');
        }
        return $this;
    }

    /**
     * access this method and this route got required a token for send a 200 OK response
     *
     * @return $this
     * @throws \Exception
     */
    public function session()
    {
        $current = false;
        $header = getallheaders();
        if (array_key_exists('X-Auth-Token', $header)) {
            $token = $header['X-Auth-Token'];
            $session = Session::get($token);
            if ($session) {
                $date = new Date($session['create_time']);
                $now = new Date();
                $dif = $date->diff($now, ['s']);
                if ($dif->seconds > $session['expires']) {
                    Session::remove($token);
                    $current = true;
                }
            } else {
                $current = true;
            }
        } else {
            $current = true;
        }
        if ($current) {
            $this->setFailedIn(401);
            $this->setStatus('Token invalid or expired, generate a new token to continue with the requisitions.');
        }
        return $this;
    }

    /**
     * add a pattern for validate params in url
     *
     * @param string                   $property
     * @param bool | string | callable $rule
     *
     * @return $this
     */
    public function where($property, $rule = false)
    {
        if (is_string($property) && (is_callable($rule) || is_string($rule))) {
            $property = str_replace('$$', '$', '$' . $property);
            /*
            $ok = false;
            foreach ($this->parsed_uri as $parsed) {
                if ($parsed['type'] === 'arg' && ($parsed['name'] === $property)) {
                    $ok = true;
                    break;
                }
            }
            if ($ok) {
            */
            if (!array_key_exists($property, $this->rules)) {
                $this->rules[$property] = [];
            }
            $this->rules[$property][] = $rule;
            /*}*/
        }
        return $this;
    }

    /**
     * @return Response|null
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return Request|null
     */
    public function request()
    {
        return $this->request;
    }

    public function reparse()
    {
        $parsed = [];
        $exp = WingedLib::explodePath($this->getUri());
        $uri = WingedLib::explodePath(App::$parentUri);
        if (!$uri) {
            $uri = [];
        }
        /*
         * parse uri
         * determine what is a value and what is a keyword
         */
        $uri_count = 0;
        if ($exp) {
            foreach ($exp as $index => $value) {
                $current = [];
                $_value = $value;
                if (begstr($value) === '{' && endstr($value) === '}') {
                    $current['type'] = 'arg';
                    $current['required'] = true;
                    begstr_replace($value);
                    endstr_replace($value, 1);
                    $_value = str_replace('?', '', $value);
                    if ($_value !== $value) {
                        $current['required'] = false;
                    }
                } else {
                    $current['type'] = 'name';
                    $current['required'] = true;
                    $uri_count++;
                }
                $current['name'] = $_value;
                $current['value'] = null;
                $current['position'] = $index;
                if (array_key_exists($index, $uri)) {
                    $current['value'] = $uri[$index];
                }
                $parsed[] = $current;
            }
        }
        $this->setParsedUri($parsed);
        $this->setUriCount($uri_count);
        return $this;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function get($uri, $callback)
    {
        $route = self::parseRegister('get', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function post($uri, $callback)
    {
        $route = self::parseRegister('post', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function put($uri, $callback)
    {
        $route = self::parseRegister('put', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function patch($uri, $callback)
    {
        $route = self::parseRegister('patch', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function options($uri, $callback)
    {
        $route = self::parseRegister('options', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function delete($uri, $callback)
    {
        $route = self::parseRegister('delete', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    public static function raw($uri, $callback)
    {
        $route = self::parseRegister('raw', $uri, $callback);
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param       $baseUri
     * @param array $routes
     *
     * @return mixed|Route
     */
    public static function group($baseUri, $routes = [])
    {
        $route = self::parseRegister(null, $baseUri, null);
        if (is_array($routes)) {
            foreach ($routes as &$groupRoute) {
                if (get_class($groupRoute) === 'Winged\Route\Route') {
                    $route->groups[] = &$groupRoute;
                }
            }
        }
        Route::$routes[$route->getName()] = &$route;
        return $route;
    }

    /**
     * @param $callback
     *
     * @return callable|Route|null
     */
    public static function notFound($callback)
    {
        if (is_callable($callback)) {
            self::$notFound = new Route('__default_not_found_route__');
            self::$notFound->setStatusCode(404);
            self::$notFound->setFailedIn(404);
            self::$notFound->setStatus('404 Not found');
            self::$notFound->setCallable($callback);
            return self::$notFound;
        }
    }

    /**
     * @param $array
     * @param $xml
     */
    public static function arrayToXml($array, &$xml)
    {
        /**
         * @var $xml \SimpleXMLElement
         */
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_int($key)) {
                    $key = "e";
                }
                $label = $xml->addChild($key);
                self::arrayToXml($value, $label);
            } else {
                $xml->addChild($key, $value);
            }
        }
    }

    /**
     * clear all errors of all routes
     */
    public static function clearAllErrorsInRoutes()
    {
        foreach (self::$routes as &$route) {
            $route->errors = [];
            $route->failedIn = false;
            $route->status = false;
            $route->statusCode = 200;
            $route->reparse();
        }
    }


    /**
     * @param $method
     * @param $uri
     * @param $callback
     *
     * @return Route
     */
    protected static function parseRegister($method, $uri, $callback)
    {
        $name = RandomName::generate('sisisi', false, false);
        $route = new Route($name);
        $route->setHttp('raw');
        if (in_array($method, ['get', 'post', 'delete', 'put', 'patch', 'options', 'raw'])) {
            $route->setHttp($method);
        }
        $uri = WingedLib::clearPath($uri);
        if (!$uri) {
            $uri = '/';
        }
        $route->setUri($uri);
        if (is_string($callback)) {
            //test if callback is string configuration for model@method
            $exp = explode('@', $callback);
            if (count7($exp) === 2) {
                $className = explode('\\', $exp[0]);
                $className = end($className);
                try {
                    $obj = new $exp[0]();
                    if (is_subclass_of($obj, 'Winged\Controller\Controller')) {
                        /**
                         * @var $obj Controller
                         */
                        $obj->route($route);
                    }
                } catch (\Exception $exception) {
                    $obj = false;
                }
                if (method_exists($obj, $exp[1])) {
                    $route->setClass($obj);
                    $route->setMethod($exp[1]);
                } else {
                    $route->setFailedIn(502);
                }
            }
            if ($route->getFailedIn() === 502) {
                $route->setStatus('Callback malformed or not configured, response from this URI ever is 502. Contact admin server or programmer of this system.');
            }
        } else if (is_array($callback)) {
            //util to create a token for future requests
            $route->setCreateSessionOptions($callback);
        } else if (is_callable($callback) || function_exists($callback)) {
            //test if callback is a function or name of a existent function
            $route->setCallable($callback);
        } else {
            $route->setFailedIn(502);
            $route->setStatus('Callback malformed or not configured, response from this URI ever is 502. Contact admin server or programmer of this system.');
        }
        //in any case of not configured callback or malformed callback throw 502 bad request
        return $route->reparse();
    }
}