<?php

namespace Winged\App;

use Exception;
use Winged\Database\Connections;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Route\RouteExec;
use Winged\Utils\WingedLib;
use Winged\Formater\Formater;

include_once "./Winged/Configs/Defines.php";
include_once "./Winged/Configs/IniSets.php";
include_once "./Winged/App/Head.php";

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
    const modelsFolder = './models/';
    const viewsFolder = './views/';
    const controllerFolder = './controllers/';

    public static $uri;
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
        $app = new File('./app.php', false);
        if (!$app->exists()) {
            $app = new File('./app.php', false);
            $app->write("<?php\n");
        }
        $this->normalize()->normalizeExtension();
        include_once $app->file_path;
    }

    public function init()
    {
        //pre_clear_buffer_die([
        //    'static::$uri' => static::$uri,
        //    'static::$pureUri' => static::$pureUri,
        //    'static::$http' => static::$http,
        //    'static::$https' => static::$https,
        //    'static::$httpParent' => static::$httpParent,
        //    'static::$httpsParent' => static::$httpsParent,
        //    'static::$protocol' => static::$protocol,
        //    'static::$protocolParent' => static::$protocolParent,
        //    'static::$fullUrlProvider' => static::$fullUrlProvider,
        //    'static::$freeGet' => static::$freeGet,
        //    'static::$host' => static::$host,
        //    'static::$haveWwwInRequest' => static::$haveWwwInRequest,
        //    'static::$haveHttpsProtocolInRequest' => static::$haveHttpsProtocolInRequest,
        //    'static::$parent' => static::$parent,
        //    'static::$isIndex' => static::$isIndex,
        //    'static::$uriParameters' => static::$uriParameters,
        //    'static::$controllerName' => static::$controllerName,
        //    'static::$controllerAction' => static::$controllerAction,
        //]);
        RouteExec::execute();
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
                if (is_directory($dir)) {
                    unset($explodedUri[$key]);
                    $beforeConcatDir = $dir;
                }
            }
            static::$parent = $beforeConcatDir;
            $beforeConcatDir = WingedLib::clearPath($beforeConcatDir);
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
                static::$parent = $dir;
                static::$isIndex = true;
            } else {
                $explodedUri = array_values($explodedUri);
                $page = $explodedUri[0];
                unset($explodedUri[0]);
                $uriParameters = [];
                foreach ($explodedUri as $key => $value) {
                    array_push($uriParameters, $value);
                }
                static::$controllerName = Formater::camelCaseClass($page);
                if (count($uriParameters) > 0) {
                    static::$controllerAction = Formater::camelCaseMethod($uriParameters[0]);
                } else {
                    static::$controllerAction = 'actionIndex';
                }
                static::$parent = $dir;
                static::$isIndex = true;
                static::$uriParameters = $uriParameters;
            }
        } else {
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

    public static function _exit(){
        Connections::closeAll();
        exit;
    }

}