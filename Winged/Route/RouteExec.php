<?php

namespace Winged\Route;

use Winged\Date\Date;
use Winged\Http\Session;
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
    public static $args = [];

    /**
     * get the founded route
     *
     * @return bool|Route
     */
    protected static function getValidRoute()
    {
        $priorityName = -1;
        $keyName = null;
        $priorityArg = -1;
        $keyArg = null;
        $rootRoute = null;
        foreach (self::$routes as $register => &$route) {
            if ($route->getUri() === '/') {
                $rootRoute = &$route;
            }
            if ($route->getPriority() > $priorityName) {
                $priorityName = $route->getPriority();
                $keyName = $register;
            }
            if ($route->getArgPriority() > $priorityArg) {
                $priorityArg = $route->getPriority();
                $keyArg = $register;
            }
        }
        if ($rootRoute && App::$uri === './') {
            return $rootRoute;
        } else {
            if ($keyName) {
                return self::$routes[$keyName];
            } else if ($keyArg) {
                return self::$routes[$keyArg];
            } else {
                return false;
            }
        }
    }

    /**
     * find a valid route comparing URI and all registred routes in ./app.php
     */
    protected static function findValidRoute()
    {
        $uri = WingedLib::clearPath(App::$uri);
        if (!$uri) {
            $uri = [];
        } else {
            $uri = explode('/', $uri);
        }
        $valid = null;
        foreach (self::$routes as $register => &$route) {
            $rules = $route->getRules();
            if ($route->getUri() === '/') {
                $registredUri = [];
            } else {
                $registredUri = explode('/', $route->getUri());
            }
            if (count($registredUri) >= count($uri)) {
                $errors = [];
                foreach ($route->getParsedUri() as $parsed) {

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

                    if ($parsed['type'] === 'name' && $parsed['name'] === $parsed['value']) {
                        $route->addPriority();
                    }
                    if ($parsed['type'] === 'arg') {
                        self::$args[] = $parsed['value'];
                        $route->addArgPriority();
                        if (is_array($rules)) {
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
                }
                $route->setErrors($errors);
            }
            if ($route->getFailedIn()) {
                $route->setStatusCode($route->getFailedIn());
            }
        }
    }


    /**
     * @return bool
     */
    public static function execute()
    {
        self::findValidRoute();
        $route = self::getValidRoute();
        if ($route) {
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
                    $return = call_user_func_array($route->getCallable(), array_merge(self::$args, [$route->getVars()]));
                } else if ($route->getClass()) {
                    $return = call_user_func_array([$route->getClass(), $route->getMethod()], array_merge(self::$args, [$route->getVars()]));
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
                if ($return) {
                    $route->response()->dispatch($return);
                } else {
                    $route->response()->setStatusCode(204);
                    $route->response()->dispatch();
                }
            }
        } else {
            if (self::$notFound) {
                $return = call_user_func_array(self::$notFound->getCallable(), array_merge(self::$args, [self::$notFound->getVars()]));
                if ($return) {
                    self::$notFound->response()->dispatch($return);
                } else {
                    self::$notFound->response()->setStatusCode(204);
                    self::$notFound->response()->dispatch();
                }
            }
        }
        return true;
    }
}