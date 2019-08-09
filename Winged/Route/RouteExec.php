<?php

namespace Winged\Route;

use Winged\Buffer\Buffer;
use Winged\Date\Date;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Http\HttpResponseHandler;
use Winged\Http\Session;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\App\App;
use WingedConfig;

/**
 * Class Route
 *
 * @package Winged\Route
 */
class RouteExec extends Route
{

    public static $args = [];

    /**
     * @return bool
     */
    public static function sendErrorResponse()
    {
        if (!empty(self::$response) && !empty(self::$routes)) {
            Error::clear();
            Buffer::kill();
            header_remove();
            switch (self::$response['response']) {
                case 502:
                    header('HTTP/1.0 ' . self::$response['response'] . ' Bad Gateway');
                    break;
                case 401:
                    header('HTTP/1.0 ' . self::$response['response'] . ' Unauthorized');
                    break;
                case 404:
                    header('HTTP/1.0 ' . self::$response['response'] . ' Not Found');
                    $file = new File(WingedConfig::$config->NOT_FOUND_FILE_PATH, false);
                    if ($file->exists()) {
                        Buffer::reset();
                        include_once WingedConfig::$config->NOT_FOUND_FILE_PATH;
                        Buffer::flushKill();
                        App::_exit();
                    }
                    break;
                case 200:
                    header('HTTP/1.0 ' . self::$response['response'] . ' OK');
                    break;
            }

            $responseHandler = new HttpResponseHandler();
            $headers = getallheaders();
            $accept = isset($headers['Accept']) ? $headers['Accept'] : 'application/json';
            switch ($accept) {
                case 'text/yaml':
                    $responseHandler->dispatchYaml(self::$response['content']);
                    break;
                case 'text/plain':
                    $responseHandler->dispatchTxt(self::$response['content']);
                    break;
                case 'text/xml':
                    $responseHandler->dispatchXml(self::$response['content']);
                    break;
                case 'application/json':
                    $responseHandler->dispatchJson(self::$response['content']);
                    break;
                case '*/*':
                    $responseHandler->dispatchJson(self::$response['content']);
                    break;
                default:
                    $responseHandler->dispatchJson(self::$response['content']);
                    break;
            }
        }
        return false;
    }


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
                            $route->setFailedIn('The route can\'t respond with found method in your http protocol.');
                        }

                        if (is_put() && $route->getHttp() != 'put') {
                            $route->setFailedIn('The route can\'t respond with found method in your http protocol.');
                        }

                        if (is_get() && $route->getHttp() != 'get') {
                            $route->setFailedIn('The route can\'t respond with found method in your http protocol.');
                        }

                        if (is_patch() && $route->getHttp() != 'patch') {
                            $route->setFailedIn('The route can\'t respond with found method in your http protocol.');
                        }

                        if (is_options() && $route->getHttp() != 'options') {
                            $route->setFailedIn('The route can\'t respond with found method in your http protocol.');
                        }

                        if (is_delete() && $route->getHttp() != 'delete') {
                            $route->setFailedIn('The route can\'t respond with found method in your http protocol.');
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
        }
    }


    /**
     * @return bool
     */
    public static function execute()
    {
        $headers = \getallheaders();
        $accept = isset($headers['Accept']) ? $headers['Accept'] : 'application/json';
        switch ($accept) {
            case 'text/yaml':
                $accept = 'yaml';
                break;
            case 'text/plain':
                $accept = 'text';
                break;
            case 'text/xml':
                $accept = 'xml';
                break;
            case 'application/json':
                $accept = 'json';
                break;
            case '*/*':
                $accept = 'json';
                break;
            default:
                $accept = 'json';
                break;
        }

        self::findValidRoute();
        $route = self::getValidRoute();
        if ($route) {
            if ($route->checkErrors()) {
                self::registerErrorResponse([
                    'response' => $route->getFailedIn(),
                    'message' => $route->getStatus(),
                    'content' => [
                        'data' => $route->getErrors()
                    ]
                ]);
            } else {
                header_remove();
                header('HTTP/1.0 200 Ok');
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

                    $responseHandler = new HttpResponseHandler();
                    switch ($accept) {
                        case 'json':
                            $responseHandler->dispatchJson($response);
                            break;
                        case 'xml':
                            $responseHandler->dispatchXml($response);
                            break;
                        case 'text':
                            $responseHandler->dispatchTxt($response);
                            break;
                        case 'yaml':
                            $responseHandler->dispatchYaml($response);
                            break;
                        default:
                            $responseHandler->dispatchJson($response);
                            break;
                    }
                }

                if (is_array($return)) {
                    $responseHandler = new HttpResponseHandler();
                    switch ($accept) {
                        case 'json':
                            $responseHandler->dispatchJson($return, false);
                            break;
                        case 'xml':
                            $responseHandler->dispatchXml($return, false);
                            break;
                        case 'text':
                            $responseHandler->dispatchTxt($return, false);
                            break;
                        case 'yaml':
                            $responseHandler->dispatchYaml($return, false);
                            break;
                        default:
                            $responseHandler->dispatchJson($return, false);
                            break;
                    }
                }
            }

        } else {
            //not found
        }
        return true;
    }
}