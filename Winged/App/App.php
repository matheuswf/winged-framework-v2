<?php

namespace Winged\App;

use Exception;
use Winged\Database\Connections;
use Winged\Directory\Directory;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Route\Route;
use Winged\Route\RouteExec;
use Winged\Utils\WingedLib;
use Winged\Formater\Formater;

$persists = 0;

if (!defined('DOCUMENT_ROOT')) {
    $document_root = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $document_root = explode('/', $document_root);
    array_pop($document_root);
    $document_root = join('/', $document_root);
    while (!file_exists($document_root . '/Winged')) {
        $document_root = explode('/', $document_root);
        array_pop($document_root);
        if (count($document_root) <= 1) {
            $persists++;
        }
        $document_root = join('/', $document_root);
        if ($persists === 2) {
            die('Die. Folder Winged not found in any location.');
        }
    }
    define('DOCUMENT_ROOT', $document_root . '/');
}

include_once DOCUMENT_ROOT . "Winged/Configs/Defines.php";
include_once DOCUMENT_ROOT . "Winged/Configs/IniSets.php";
include_once DOCUMENT_ROOT . "Winged/App/Head.php";

try {
    Head::init();
} catch (Exception $exception) {
    Error::display();
}

/**
 * This class its a main class of Winged
 *
 * @version       2.0.0.0
 * @access        public static object
 * @author        Matheus Prado Rodrigues
 * @copyright (c) 2019, Winged Framework
 */
class App
{
    /**
     * @var $response Response
     */
    public static $response;

    /**
     * @var $response Request
     */
    public static $request;

    public static $uri;
    public static $parentUri;
    public static $pureUri;
    public static $http;
    public static $https;
    public static $httpParent;
    public static $httpsParent;
    public static $protocol;
    public static $protocolParent;
    public static $fullUrlProvider;
    public static $freeGet;
    public static $host;
    public static $haveWwwInRequest = false;
    public static $haveHttpsProtocolInRequest = false;

    public static $parent;
    public static $isIndex;
    public static $uriParameters = [];
    public static $controllerName;
    public static $controllerAction;

    public function __construct()
    {
        static::$request = new Request();
        static::$response = new Response(static::$request);
        $this->normalize()->normalizeExtension();
        $app = new File(App::$parent . 'app.php', false);
        if (!$app->exists()) {
            $app = new File(App::$parent . 'app.php', false);
            $app->write("<?php\n");
        }
        include_once $app->file_path;
        $this->autoRegisterController();
    }

    public function init()
    {
        RouteExec::execute();
    }

    /**
     * checks if the URI matches an existing Controller, if it matches, registers the URI as a Route pointing to the Controller
     */
    protected function autoRegisterController()
    {
        $controllerInfo = $this->getController();
        if ($controllerInfo) {
            $uri = WingedLib::clearPath(static::$parentUri);
            $baseUri = '';
            if (!$uri) {
                $baseUri = './';
            } else {
                $uri = explode('/', $uri);
                if (isset($uri[0])) {
                    $baseUri = './' . $uri[0] . '/';
                }
                if (isset($uri[1])) {
                    $baseUri .= $uri[1] . '/';
                } else {
                    $baseUri .= 'index/';
                }
            }
            if (!empty($controllerInfo['params'])) {
                foreach ($controllerInfo['params'] as $paramName => $optional) {
                    if ($optional) {
                        $baseUri .= '{$' . $paramName . '?}/';
                    } else {
                        $baseUri .= '{$' . $paramName . '}/';
                    }
                }
            }
            Route::raw($baseUri, static::$controllerName . '@' . static::$controllerAction)->addResponseMiddleware(new ResponseMiddleware(function ($response) {
                if (is_array($response)) {
                    /**
                     * @var $this ResponseMiddleware
                     */
                    $self = &$this;
                    if ($self->getRoute()->request()->isAcceptableType('application/json')) {
                        $self->getRoute()->forceJson();
                    } else if ($self->getRoute()->request()->isAcceptableType('application/xml')) {
                        $self->getRoute()->forceXml();
                    } else if ($self->getRoute()->request()->isAcceptableType('application/yaml')) {
                        $self->getRoute()->forceYaml();
                    }
                }
            }));
        }
    }

    /**
     * checks if Controller exists and return informations of it
     *
     * @return array|bool
     */
    protected function getController()
    {
        $uri = WingedLib::clearPath(static::$parentUri);
        if (!$uri) {
            $uri = './';
            $explodedUri = ['index', 'index'];
        } else {
            $explodedUri = explode('/', $uri);
            if (count($explodedUri) == 1) {
                $uri = './' . $explodedUri[0] . '/';
            } else {
                $uri = './' . $explodedUri[0] . '/' . $explodedUri[1] . '/';
            }
        }

        $indexUri = WingedLib::clearPath(\WingedConfig::$config->INDEX_ALIAS_URI);
        if ($indexUri) {
            $indexUri = explode('/', $indexUri);
        }

        if ($indexUri) {
            if ($explodedUri[0] === 'index' && isset($indexUri[0])) {
                static::$controllerName = Formater::camelCaseClass($indexUri[0]) . 'Controller';
                $uri = './' . $indexUri[0] . '/';
            }
            if (isset($explodedUri[1]) && isset($indexUri[1])) {
                if ($explodedUri[1] === 'index') {
                    static::$controllerAction = 'action' . Formater::camelCaseMethod($indexUri[1]);
                    $uri .= $indexUri[1] . '/';
                }
            } else {
                $uri .= 'index/';
            }
        }

        $controllerDirectory = new Directory(static::$parent . 'controllers/', false);
        if ($controllerDirectory->exists()) {
            $controllerFile = new File($controllerDirectory->folder . static::$controllerName . '.php', false);
            if ($controllerFile->exists()) {
                include_once $controllerFile->file_path;
                if (class_exists(static::$controllerName)) {
                    $controller = new static::$controllerName();
                    if (method_exists($controller, static::$controllerAction)) {
                        try {
                            $reflectionMethod = new \ReflectionMethod(static::$controllerName, static::$controllerAction);
                            $pararms = [];
                            foreach ($reflectionMethod->getParameters() as $parameter) {
                                $pararms[$parameter->getName()] = $parameter->isOptional();
                            }
                        } catch (\Exception $exception) {
                            $pararms = [];
                        }
                        return [
                            'uri' => $uri,
                            'params' => $pararms,
                        ];
                    }
                }
            }
        }
        return false;
    }

    /**
     * normalize uri and ignore domain name and all folder before root of application
     */
    private function normalize()
    {
        $finalUri = '';
        $siteName = '';
        $baseUri = server("request_uri");
        $freeGet = explode("?", $baseUri);
        if (count7($freeGet) > 1) {
            static::$freeGet = $freeGet[1];
        } else {
            static::$freeGet = '';
        }
        $indexFolder = WingedLib::explodePath(WingedLib::clearDocumentRoot());
        $indexFolder = end($indexFolder);
        $explodedUri = WingedLib::explodePath($freeGet[0]);
        $rootFoundInUri = false;
        if ($indexFolder && $explodedUri) {
            if (in_array($indexFolder, $explodedUri)) {
                $rootFoundInUri = true;
            }
        }
        static::$host = WingedLib::convertslash(server("server_name"));
        if (is_int(stripos(static::$host, 'www.'))) {
            static::$haveWwwInRequest = true;
        }
        $changeConcat = false;
        if ($explodedUri) {
            foreach ($explodedUri as $uri) {
                if (!$changeConcat && $rootFoundInUri) {
                    $siteName .= '/' . $uri;
                } else {
                    $finalUri .= '/' . $uri;
                }
                if ($uri === $indexFolder) {
                    $changeConcat = true;
                }
            }
        }
        $siteName = WingedLib::clearPath($siteName);
        $finalUri = WingedLib::normalizePath($finalUri);
        static::$https = "https://" . static::$host . '/' . $siteName . "/";
        static::$http = "http://" . static::$host . '/' . $siteName . "/";
        static::$uri = $finalUri;
        if (count7($freeGet) > 1) {
            static::$pureUri = $finalUri . '?' . $freeGet[1];
        } else {
            static::$pureUri = $finalUri;
        }
        if (server('https')) {
            if (server('https') != 'off') {
                static::$protocol = static::$https;
                static::$haveHttpsProtocolInRequest = true;
            } else {
                static::$protocol = static::$http;
                static::$haveHttpsProtocolInRequest = false;
            }
        } else {
            static::$protocol = static::$http;
            static::$haveHttpsProtocolInRequest = false;
        }
        if (server('http_x_forwarded_proto') != false) {
            if (server('http_x_forwarded_proto') === 'https') {
                static::$haveHttpsProtocolInRequest = true;
                static::$protocol = static::$https;
            }
        }
        return $this;
    }

    /**
     * extension of normalize, this determine parent directory name of controller and action founded in uri
     *
     * @return $this
     */
    private function normalizeExtension()
    {
        $explodedUri = WingedLib::explodePath(static::$uri);
        $dir = WingedLib::normalizePath();
        $beforeConcatDir = WingedLib::normalizePath();
        if ($explodedUri) {
            foreach ($explodedUri as $key => $uriPart) {
                $dir .= $uriPart;
                $dir = WingedLib::normalizePath($dir);
                if (is_directory(WingedLib::clearPath(DOCUMENT_ROOT . $dir) . '/')) {
                    unset($explodedUri[$key]);
                    $beforeConcatDir = $dir;
                }
            }
            static::$parent = $beforeConcatDir;
            $beforeConcatDir = WingedLib::clearPath($beforeConcatDir);

            static::$parentUri = WingedLib::clearPath(WingedLib::clearPath(str_replace($beforeConcatDir, '', static::$uri)));
            if (!static::$parentUri) {
                static::$parentUri = './';
            } else {
                static::$parentUri = './' . static::$parentUri . '/';
            }

            if (static::$uri != static::$parentUri) {
                $explodedUri = WingedLib::explodePath(static::$parentUri);
            }

            static::$httpsParent = static::$https . $beforeConcatDir . '/';
            static::$httpParent = static::$http . $beforeConcatDir . '/';
            static::$protocolParent = static::$protocol . $beforeConcatDir . '/';

            if (static::$freeGet != '') {
                static::$fullUrlProvider = static::$protocol . WingedLib::clearPath(static::$pureUri) . '?' . static::$freeGet;
            } else {
                static::$fullUrlProvider = static::$protocol . WingedLib::clearPath(static::$pureUri);
            }
            if (count7($explodedUri) == 0) {
                static::$controllerName = 'IndexController';
                static::$controllerAction = 'actionIndex';
                static::$isIndex = true;
            } else {
                $explodedUri = array_values($explodedUri);
                $page = $explodedUri[0];
                unset($explodedUri[0]);
                $uriParameters = [];
                foreach ($explodedUri as $key => $value) {
                    array_push($uriParameters, $value);
                }
                static::$controllerName = Formater::camelCaseClass($page) . 'Controller';
                if (count($uriParameters) > 0) {
                    static::$controllerAction = 'action' . Formater::camelCaseMethod($uriParameters[0]);
                } else {
                    static::$controllerAction = 'actionIndex';
                }
                static::$isIndex = true;
                static::$uriParameters = $uriParameters;
            }
        } else {
            static::$parentUri = './';
            static::$httpsParent = static::$https;
            static::$httpParent = static::$http;
            static::$protocolParent = static::$protocol;
            if (static::$freeGet != '') {
                static::$fullUrlProvider = static::$protocol . WingedLib::clearPath(static::$pureUri) . '?' . static::$freeGet;
            } else {
                static::$fullUrlProvider = static::$protocol . WingedLib::clearPath(static::$pureUri);
            }
            static::$isIndex = true;
            static::$controllerName = 'IndexController';
            static::$controllerAction = 'actionIndex';
            static::$parent = './';
        }
        return $this;
    }

    /**
     * @return Request
     */
    public static function getRequest()
    {
        return self::$request;
    }

    /**
     * @return Response
     */
    public static function getResponse()
    {
        return self::$response;
    }

    protected static function parseUriIntoControllerNames()
    {
        $explodedUri = WingedLib::explodePath(static::$parentUri);
        if ($explodedUri) {
            if (count7($explodedUri) == 0) {
                static::$controllerName = 'IndexController';
                static::$controllerAction = 'actionIndex';
                static::$isIndex = true;
            } else {
                $explodedUri = array_values($explodedUri);
                $page = $explodedUri[0];
                unset($explodedUri[0]);
                $uriParameters = [];
                foreach ($explodedUri as $key => $value) {
                    array_push($uriParameters, $value);
                }
                static::$controllerName = Formater::camelCaseClass($page) . 'Controller';
                if (count($uriParameters) > 0) {
                    static::$controllerAction = 'action' . Formater::camelCaseMethod($uriParameters[0]);
                } else {
                    static::$controllerAction = 'actionIndex';
                }
                static::$isIndex = true;
                static::$uriParameters = $uriParameters;
            }
        }
    }

    /**
     * @param bool $newUri
     *
     * @return bool
     */
    public static function virtualizeUri($newUri = false)
    {
        if ($newUri) {
            self::$parentUri = './' . WingedLib::clearPath($newUri) . '/';
            try {
                self::parseUriIntoControllerNames();
                $reflectionClass = new \ReflectionClass(__CLASS__);
                $instance = $reflectionClass->newInstanceWithoutConstructor();
                /**
                 * @var $instance App
                 */
                $instance->autoRegisterController();
                Route::clearAllErrorsInRoutes();
                RouteExec::execute();
            } catch (\Exception $exception) {
                return false;
            }
        }
    }

    public static function _exit()
    {
        Connections::closeAll();
        exit;
    }

}