<?php

namespace Winged\Route;

use Winged\App\RequestMiddleware;
use Winged\App\ResponseMiddleware;
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
class RouteExec extends Route
{

    public static $filteredRoutes = null;
    public static $currentRoute = null;

    /**
     * find a valid route comparing URI and all registred routes in ./app.php
     */
    protected static function findValidRoute()
    {
        $uri = WingedLib::clearPath(App::$parentUri);
        if (!$uri) {
            $uri = [];
        } else {
            $uri = explode('/', $uri);
        }

        /**
         * @var $rootRoute null | Route
         */
        $rootRoute = null;

        /**
         * register an countable number, where key is a name of Route
         * if name in URI match with name in Route URI adds + 2
         * if name in URI match with param in Route URI adds + 1
         *
         * @var $valids array
         */
        $valids = [];

        //for each registred Route
        foreach (self::$routes as $register => &$route) {
            foreach ($route->responseMiddleware as $key => $middleware) {
                $middleware->setRoute($route);
            }
            foreach ($route->requestMiddleware as $key => $middleware) {
                $middleware->setRoute($route);
            }
            //if Route responds by / in URI
            if ($route->getUri() === '/') {
                $rootRoute = &$route;
            }
            $valids[$route->getName()] = 0;
            foreach ($route->getParsedUri() as $uriPart) {
                //check if the position in registred URI of Route existes in current request URI
                if (array_key_exists($uriPart['position'], $uri)) {
                    //if name and names in both positions are equal adds + 2
                    if ($uriPart['type'] === 'name') {
                        if ($uri[$uriPart['position']] === $uriPart['name']) {
                            $valids[$route->getName()] += 2;
                        }
                        //else is an args and adds + 1
                    } else if ($uriPart['type'] === 'arg') {
                        $valids[$route->getName()]++;
                    }
                }
            }
        }

        $possibleRoutes = [];
        //make an copy of valids for sort purpose
        $forSort = $valids;
        sort($forSort);
        if (!empty($valids)) {
            //$big is the largest number found after handling all Routes
            $big = end($forSort);
            //if not found route and exists Route for URI = /
            if ($big === 0 && $rootRoute) {
                //if any exists in URI can't return root Route
                if (count($uri) > 0 && empty($rootRoute->getParsedUri())) {
                    return false;
                }
                return $rootRoute;
            } else {
                //each all caculated Routes
                foreach ($valids as $key => $value) {
                    //if current value equal largest value founded in $valids
                    if ($big === $value) {
                        //$key is the name of registred Route in self::$routes
                        $explodedUri = WingedLib::clearPath(self::$routes[$key]->getUri());
                        if (!$explodedUri) {
                            $explodedUri = [];
                        } else {
                            $explodedUri = explode('/', $explodedUri);
                        }
                        //after explode URI
                        $parsedUri = self::$routes[$key]->getParsedUri();
                        if (!empty($parsedUri)) {
                            //if count od request URI and registred URI are equal, so is a valid Route
                            if (count($uri) == count($explodedUri)) {
                                $possibleRoutes[] = &self::$routes[$key];
                            } else if (count($uri) < count($explodedUri)) {
                                //if count of request URI is smaller of registred URI
                                $add = 0;
                                $valid = true;
                                foreach ($parsedUri as $uriName => $uriInfo) {
                                    if ($add >= count($uri)) {
                                        //check here if arguments in registred URI have type equal "arg" and it is not "required"
                                        if ($uriInfo['type'] === 'name') $valid = false;
                                        if ($uriInfo['type'] === 'arg' && $uriInfo['required']) $valid = false;
                                    }
                                    $add++;
                                }
                                //if pass in all conditions on last looping
                                if ($valid) {
                                    $possibleRoutes[] = &self::$routes[$key];
                                }
                            }
                        }
                    }
                }
            }
        }

        $filteredRoutes = [];

        //run in $possibleRoutes
        foreach ($possibleRoutes as $register => &$route) {

            $rules = $route->getRules();
            $errors = [];

            //check for HTTP method
            if ($route->getHttp() !== 'raw') {

                if (is_post() && $route->getHttp() != 'post') {
                    $route->setFailedIn(405);
                    $route->setStatus('The route can\'t respond with found method in your http protocol.');
                }

                if (is_put() && $route->getHttp() != 'put') {
                    $route->setFailedIn(405);
                    $route->setStatus('The route can\'t respond with found method in your http protocol.');
                }

                if (is_get() && $route->getHttp() != 'get') {
                    $route->setFailedIn(405);
                    $route->setStatus('The route can\'t respond with found method in your http protocol.');
                }

                if (is_patch() && $route->getHttp() != 'patch') {
                    $route->setFailedIn(405);
                    $route->setStatus('The route can\'t respond with found method in your http protocol.');
                }

                if (is_options() && $route->getHttp() != 'options') {
                    $route->setFailedIn(405);
                    $route->setStatus('The route can\'t respond with found method in your http protocol.');
                }

                if (is_delete() && $route->getHttp() != 'delete') {
                    $route->setFailedIn(405);
                    $route->setStatus('The route can\'t respond with found method in your http protocol.');
                }

            }

            //check for rules
            if (is_array($rules) && !empty($rules)) {
                foreach ($route->getParsedUri() as $parsed) {
                    if (array_key_exists($parsed['name'], $rules)) {
                        foreach ($rules[$parsed['name']] as $rule) {
                            if (is_callable($rule)) {
                                if (!call_user_func_array($rule, [$parsed['value']])) {
                                    $route->setFailedIn(502);
                                    $route->setStatus('Argument validation error.');
                                    if (!array_key_exists($parsed['name'], $errors)) {
                                        $errors[$parsed['name']] = [];
                                    }
                                    $errors[$parsed['name']][] = 'Argument ' . $parsed['name'] . ' not pass on seted rule [callable]';
                                }
                            } else if (!preg_match('/' . $rule . '/', $parsed['value'], $matches)) {
                                $route->setFailedIn(502);
                                $route->setStatus('Argument validation error.');
                                if (!array_key_exists($parsed['name'], $errors)) {
                                    $errors[$parsed['name']] = [];
                                }
                                $errors[$parsed['name']][] = 'Argument ' . $parsed['name'] . ' not pass on seted rule [' . $rule . ']';
                            }
                        }
                    }
                }
            }

            //set errors or empty array
            $route->setErrors($errors);

            //if no have error and not failed in rules or http requested method
            if (empty($errors) && !$route->getFailedIn()) {
                $filteredRoutes[] = &$route;
            }
        }

        //if empty possible routes, filteredRoutes it is too, then return false = not found
        if (empty($possibleRoutes)) {
            return false;
        }

        static::$filteredRoutes = $possibleRoutes;

        //if empty filtered routes, return first route founded in possibleRoute, it have errors
        if (empty($filteredRoutes)) {
            return $possibleRoutes[0];
        }

        //return corret route founded and filtered, without errors
        static::$currentRoute = 0;
        return $filteredRoutes[0];
    }


    protected static function parseGroups()
    {
        //run inside registred Routes and check if the route have others Routes in property $groups
        foreach (self::$routes as $register => &$route) {
            //if grouped Routes
            if (!empty($route->getGroups())) {
                //get all Routes in group
                $groupedRoutes = $route->getGroups();
                //for each Route in group
                foreach ($groupedRoutes as &$groupRoute) {
                    //if current grouped Route have an object@method as callback, register new Route with other name and merge parent URI of father Route with child Route
                    if ($groupRoute->getClass()) {
                        $newRoute = self::parseRegister($groupRoute->getHttp(), $route->getUri() . '/' . $groupRoute->getUri(), get_class($groupRoute->getClass()) . '@' . $groupRoute->getMethod());
                        //same, but for param like a function / callable
                    } else if ($groupRoute->getCallable()) {
                        $newRoute = self::parseRegister($groupRoute->getHttp(), $route->getUri() . '/' . $groupRoute->getUri(), $groupRoute->getCallable());
                        //else callback is an array with session properties
                    } else {
                        $newRoute = self::parseRegister($groupRoute->getHttp(), $route->getUri() . '/' . $groupRoute->getUri(), $groupRoute->getCreateSessionOptions());
                    }
                    //unset old child grouped Route from array of Routes
                    if (array_key_exists($groupRoute->getName(), self::$routes)) {
                        unset(self::$routes[$groupRoute->getName()]);
                    }
                    //preserve all properties of old child Route into new Route
                    $newRoute->setName($groupRoute->getName());
                    $newRoute->setRules(array_merge($route->getRules(), $groupRoute->getRules()));
                    $newRoute->setVars(array_merge($route->getVars(), $groupRoute->getVars()));
                    $newRoute->setOrigins(array_merge($route->getOrigins(), $groupRoute->getOrigins()));
                    $newRoute->setRequestMiddleware(array_merge($route->getRequestMiddleware(), $groupRoute->getResponseMiddleware()));
                    $newRoute->setResponseMiddleware(array_merge($route->getResponseMiddleware(), $groupRoute->getResponseMiddleware()));
                    $newRoute->request = $groupRoute->request;
                    $newRoute->response->request = $groupRoute->request;
                    if (count($newRoute->request->accept) === count($route->request->accept)) {
                        $newRoute->request = $route->request;
                        $newRoute->response->request = $route->request;
                    }
                    //set new Route into array of Routes
                    self::$routes[$newRoute->getName()] = $newRoute;
                }
                //unset the father Route
                unset(self::$routes[$route->getName()]);
            }
        }
    }

    /**
     * @param bool $routeIndex
     *
     * @return bool
     */
    public static function execute($routeIndex = false)
    {
        $route = false;
        if (!$routeIndex) {
            self::parseGroups();
            $route = self::findValidRoute();
        } else {
            if (!empty(self::$filteredRoutes)) {
                if (array_key_exists($routeIndex, self::$filteredRoutes)) {
                    $route = self::$filteredRoutes[$routeIndex];
                }
            }
        }
        if ($route) {
            /**
             * @var $middleware RequestMiddleware
             */
            foreach ($route->getRequestMiddleware() as $middleware) {
                call_user_func_array($middleware->getCallback(), []);
            }

            $args = [];
            foreach ($route->getParsedUri() as $parsed) {
                if ($parsed['type'] === 'arg') {
                    $args[] = $parsed['value'];
                }
            }
            if ($route->checkErrors()) {
                $route->response()->setStatusCode((int)$route->getStatusCode());
                $route->response()->dispatch([
                    'response' => $route->getFailedIn(),
                    'message' => $route->getStatus(),
                    'content' => [
                        'data' => $route->getErrors()
                    ]
                ], true);
            } else {
                $route->response()->setStatusCode((int)$route->getStatusCode());
                $return = null;
                if ($route->getCallable()) {
                    $return = call_user_func_array($route->getCallable(), array_merge($args, [$route->getVars()]));
                } else if ($route->getClass()) {
                    $return = call_user_func_array([$route->getClass(), $route->getMethod()], array_merge($args, [$route->getVars()]));
                } else {
                    $token = RandomName::generate('sisisisisisi', true, false);
                    $expires = isset($route->getCreateSessionOptions()['expires']) ? $route->getCreateSessionOptions()['expires'] : 3600;
                    $session = [
                        'create_time' => (new Date())->dmy(),
                        'expires' => $expires
                    ];
                    Session::set($token, $session);

                    $response = [
                        'token' => [
                            'status' => true,
                            'name' => $token,
                            'expires' => $expires
                        ]
                    ];
                    $route->response()->dispatch($response);
                }

                /**
                 * @var $middleware ResponseMiddleware
                 */
                foreach ($route->getResponseMiddleware() as $middleware) {
                    $parsed = call_user_func_array($middleware->getCallback(), [$return]);
                    if ($parsed) {
                        $return = $parsed;
                    }
                }

                if($route->getContinue()){
                    $nextIndex = self::$currentRoute + 1;
                    if (array_key_exists($nextIndex, self::$filteredRoutes)) {
                        return self::execute($nextIndex);
                    }
                }

                if ($return) {
                    $route->response()->dispatch($return);
                } else {
                    $route->response()->setStatusCode(204);
                    $route->response()->dispatch();
                }
            }
        } else {
            if (self::$notFound) {
                $args = WingedLib::clearPath(App::$uri);
                if (!$args) {
                    $args = [];
                } else {
                    $args = explode('/', $args);
                }
                $return = call_user_func_array(self::$notFound->getCallable(), array_merge($args, [self::$notFound->getVars()]));
                if ($return) {
                    self::$notFound->response()->dispatch($return);
                } else {
                    self::$notFound->response()->setStatusCode(204);
                    self::$notFound->response()->dispatch();
                }
            } else {
                App::getResponse()->setStatusCode(404);
                App::getResponse()->dispatchJson([
                    'status' => false,
                    'content' => 'Nothing exists here. Malformed not found default page or not configured.'
                ]);
            }
        }
        return true;
    }
}