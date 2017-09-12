<?php

ini_set('memory_limit', '2048M');

define("PARENT_DIR_PAGE_NAME", 1);
define("ROOT_ROUTES_PAGE_NAME", 2);
define("PARENT_ROUTES_ROUTE_PHP", 3);
define("ROOT_ROUTES_ROUTE_PHP", 4);

define("USE_PREPARED_STMT", true);
define("NO_USE_PREPARED_STMT", false);
define("IS_PDO", "PDO");
define("IS_MYSQLI", "MYSQLI");

define("DB_DRIVER_CUBRID", "cubrid:host=%s;port=%s;dbname=%s"); //host, port, dbname, user pass
define("DB_DRIVER_FIREBIRD", "firebird:dbname=%s/%s:%s"); //host(dmname), port(dbname), file_path, user, pass
define("DB_DRIVER_MYSQL", "mysql:host=%s;port=%s;dbname=%s"); //host || unix_socket, port, dbname, user, pass
define("DB_DRIVER_MYSQL_UNIX", "mysql_unix:unix_socket=%s;port=%s;dbname=%s"); //host || unix_socket, port, dbname, user, pass
define("DB_DRIVER_SQLSRV", "sqlsrv:Server=%s,%s;Database=%s"); //host, port, dbname, user, pass
define("DB_DRIVER_PGSQL", "pgsql:dbname=%s;host=%s"); //dbname, host, user, pass
define("DB_DRIVER_SQLITE", "sqlite:%s"); //dbname

define("PATH_CONFIG", "./config.php");
define("PATH_EXTRA_CONFIG", "./extra.config.php");
define("PATH_INSTALL", "./winged/");
define("CLASS_PATH", "./winged/classes/");
define("STD_CONFIG", "./winged/config/config.php");
define("STD_ROUTES", "./winged/routes/");

include_once PATH_INSTALL . 'utils/functions.php';

ini_set("display_errors", true);

ini_set("display_startup_errors", true);

umask(0);

clearstatcache();

Winged::obStart();

register_shutdown_function("winged_shutdown_handler");

set_error_handler("winged_error_handler", E_ALL | E_PARSE | E_ERROR);

global $_OPOST, $_OGET, $beggin_time;

$_OPOST = $_POST;
$_OGET = $_GET;

include_once(CLASS_PATH . "external/phpQuery.php");

include_once(CLASS_PATH . "autoload/winged.autoload.php");
include_once(PATH_INSTALL . "utils/winged.lib.php");
include_once(CLASS_PATH . "rewrite/winged.rewrite.php");
include_once(CLASS_PATH . "rewrite/winged.parameter.php");
include_once(CLASS_PATH . "controller/winged.assets.php");
include_once(CLASS_PATH . "controller/winged.controller.php");
include_once(CLASS_PATH . "restful/winged.restful.php");
include_once(CLASS_PATH . "session/winged.session.php");
include_once(CLASS_PATH . "date/winged.date.php");
include_once(CLASS_PATH . "date/winged.microtime.php");
include_once(CLASS_PATH . "email/winged.email.php");
include_once(CLASS_PATH . "file/winged.download.php");
include_once(CLASS_PATH . "file/winged.upload.php");
include_once(CLASS_PATH . "string/winged.string.php");
include_once(CLASS_PATH . "string/winged.translate.php");
include_once(CLASS_PATH . "token/winged.tokanizer.php");
include_once(CLASS_PATH . "file/winged.fileutils.php");
include_once(CLASS_PATH . "file/winged.img.file.php");
include_once(CLASS_PATH . "http/winged.response.php");
include_once(CLASS_PATH . "http/winged.request.php");
include_once(CLASS_PATH . "form/winged.form.html.store.php");
include_once(CLASS_PATH . "form/winged.form.php");
include_once(CLASS_PATH . "validator/winged.validator.php");
include_once(CLASS_PATH . "formater/winged.formater.php");

Microtime::init();

function __autoload($class)
{
    return WingedAutoLoad::verify($class);
}

if (!file_exists(PATH_CONFIG)) {
    $local = server("request_uri");
    $exp = explode("/", $local);
    if (!in_array("winged", $exp)) {
        header("Location: " . PATH_INSTALL);
    } else {
        include_once STD_CONFIG;
    }
    if (file_exists(PATH_EXTRA_CONFIG)) {
        include_once PATH_EXTRA_CONFIG;
    }
} else {
    include_once PATH_CONFIG;
    if (file_exists(PATH_EXTRA_CONFIG)) {
        include_once PATH_EXTRA_CONFIG;
    }
    if (WingedConfig::$DBEXT) {
        include_once(CLASS_PATH . "database/winged.db.php");
        include_once(CLASS_PATH . "database/winged.querybuilder.php");
        include_once(CLASS_PATH . "database/winged.delegate.php");
        include_once(CLASS_PATH . "database/winged.migrate.php");
        include_once(CLASS_PATH . "database/winged.db.dict.php");
        include_once(CLASS_PATH . "model/winged.model.php");

        Connections::init();

        $_GET = no_injection_array($_OGET);
        $_POST = no_injection_array($_OPOST);
    }

    if (!is_null(WingedConfig::$TIMEZONE)) {
        date_default_timezone_set(WingedConfig::$TIMEZONE);
    } else {
        date_default_timezone_set("Brazil/West");
    }

    if (!is_null(WingedConfig::$INCLUDES)) {
        if (gettype(WingedConfig::$INCLUDES) == "array") {
            for ($x = 0; $x < count(WingedConfig::$INCLUDES); $x++) {
                include_once WingedConfig::$INCLUDES[$x];
            }
        } else {
            include_once WingedConfig::$INCLUDES;
        }
    }
}

if (WingedConfig::$DEBUG) {
    error_reporting(E_ALL);
} else {
    error_reporting(E_WARNING | E_NOTICE | E_ERROR);
}

mb_internal_encoding(WingedConfig::$INTERNAL_ENCODING);
mb_http_output(WingedConfig::$OUTPUT_ENCODING);
header('Content-type: ' . WingedConfig::$MAIN_CONTENT_TYPE . '; charset=' . WingedConfig::$HTML_CHARSET . '');

/**
 * This class its a main class of Winged
 * @version 1.8.3.5
 * @access public static object
 * @author Matheus Prado Rodrigues
 * @copyright (c) 2017, Winged Framework
 */
class Container
{
    protected $target;
    protected $className;
    protected $methods = [];

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function attach($name, $method)
    {
        if (!$this->className) {
            $this->className = get_class($this->target);
        }
        $binded = Closure::bind($method, $this->target, $this->className);
        $this->methods[$name] = $binded;
    }

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->methods)) {
            return call_user_func_array($this->methods[$name], $arguments);
        }

        if (method_exists($this->target, $name)) {
            return call_user_func_array(
                array($this->target, $name),
                $arguments
            );
        }
    }
}

class Winged
{

    public static $standard;
    public static $standard_controller;
    public static $controller_page;
    public static $controller_action;
    public static $controller_debug = true;
    public static $http;
    public static $https;
    public static $protocol;
    public static $uri = false;
    public static $pure_uri = false;
    public static $page;
    public static $parent = false;
    public static $params = array();
    public static $oparams = array();
    public static $controller_params = array();
    public static $key;
    public static $page_surname;
    public static $routed_file;
    public static $router = 1;
    public static $routes = array();
    public static $restful = false;
    public static $route_dir;
    /**
     * @var $rewrite_obj Rewrite
     */
    public static $rewrite_obj = false;
    /**
     * @var $restful_obj Restful
     */
    public static $restful_obj;
    /**
     * @var $controller Controller
     */
    public static $controller;
    public static $geted_file;
    public static $reset;
    public static $notfound;
    public static $is_standard = false;
    public static $ob_buffer = false;
    private static $warnings = array();
    private static $errors = array();
    private static $ignore_errors = array("E_DEPRECATED");


    /**
     * @access public
     * @example Winged::start() this method starts all of winged, don't call this method again.
     * @return void
     */
    public static function start()
    {
        if (!self::$rewrite_obj) {
            self::$rewrite_obj = new Rewrite();
            self::$restful_obj = new Restful();
            self::$controller = new Controller();
        }
        if (is_null(WingedConfig::$NOTFOUND) || !WingedConfig::$NOTFOUND) {
            WingedConfig::$NOTFOUND = "./winged/class/rewrite/error/404.php";
        }
        self::$controller_debug = (WingedConfig::$CONTROLLER_DEBUG !== null) ? WingedConfig::$CONTROLLER_DEBUG : true;
        if (is_null(WingedConfig::$STANDARD)) {
            self::$standard = WingedConfig::$STANDARD;
            self::$notfound = WingedConfig::$NOTFOUND;
        } else {
            self::$notfound = WingedConfig::$NOTFOUND;
            self::$standard = WingedConfig::$STANDARD;
            self::$standard_controller = WingedConfig::$STANDARD_CONTROLLER;
            self::$router = WingedConfig::$ROUTER;
        }
        self::nosplit();
    }

    public static function nosplit()
    {
        self::normalize();
        self::restful();

        $arr_ext = ['.php', '.html', '.htm', '.xml', '.json'];

        if (WingedConfig::$NOT_WINGED) {
            $dirs = self::getdir(wl::dotslash(self::$uri), 'pure-html');
            self::$parent = $dirs['parent'];
            self::$page_surname = $dirs['page'];
            foreach ($arr_ext as $ext) {
                if (file_exists(self::$parent . self::$page_surname . $ext)) {
                    include_once self::$parent . self::$page_surname . $ext;

                    exit;
                }
            }
        }

        $dirs = self::getdir(wl::dotslash(self::$uri));

        $page = trim($dirs["page"]);

        $parent = wl::dotslash(wl::dotslash(trim($dirs["parent"])), true);
        $params = $dirs["params"];

        self::$key = $parent . $page . "/";

        self::$page_surname = $page;
        self::$page = $page;
        self::$parent = $parent;
        self::$params = $params;
        self::$controller_params = $params;

        $vect = self::return_path_route();

        self::$page = $vect["page"];
        self::$routed_file = DOCUMENT_ROOT . str_replace('./', '', $vect["file"]);
        self::$route_dir = DOCUMENT_ROOT . str_replace('./', '', $vect["dir"]);

        $controller_info = self::controller_info();

        self::$controller_page = $controller_info['controller'];
        self::$controller_action = $controller_info['action'];

        if (!self::$restful) {
            $found = self::$controller->find();
            if (!$found) {
                self::$rewrite_obj->rewrite_page();
            }
        }

    }

    private static function controller_info()
    {
        $exp = wl::slashexplode(Winged::$parent);
        $uri = wl::slashexplode(self::$uri);
        $nar = [];

        if (isset($exp[0]) && $exp[0] == '') {
            return ['controller' => self::$standard_controller, 'action' => 'index'];
        } else {
            for ($i = 0; $i < count($uri); $i++) {
                if (!in_array($uri[$i], $exp)) {
                    $nar[] = $uri[$i];
                }
            }
            if (count($nar) == 0) {
                return ['controller' => self::$standard_controller, 'action' => 'index'];
            } else if (count($nar) == 1) {
                return ['controller' => $nar[0], 'action' => 'index'];
            } else {
                return ['controller' => $nar[0], 'action' => $nar[1]];
            }
        }
    }

    public static function normalize()
    {

        $b_uri = server("request_uri");
        $free_get = explode("?", $b_uri);
        $uri = wl::convertslash($free_get[0]);
        $self = wl::convertslash(server("php_self"));
        $host = wl::convertslash(server("server_name"));

        $uris = wl::slashexplode($uri);
        $selfs = wl::slashexplode($self);

        $self = "";
        $lastself = "";
        for ($x = 0; $x < count($selfs); $x++) {
            if ($selfs[$x] != "index.php") {
                if ($x == 0) {
                    $self = $selfs[$x];
                } else {
                    $self .= "/" . $selfs[$x];
                }
                $lastself = $selfs[$x];
            }
        }

        $fix = false;
        $cont = 0;
        $find = 0;
        $inarray = array();

        for ($x = 0; $x < count($uris); $x++) {
            if ($uris[$x] == $lastself || $lastself == "") {
                $fix = true;
                array_push($inarray, $lastself);
            }

            $str_count = count($inarray);

            if (($fix && $uris[$x] != $lastself) || ($fix && $str_count >= 2)) {
                if ($cont == 0) {
                    $uri = "./" . $uris[$x];
                    $cont++;
                } else {
                    $uri .= "/" . $uris[$x];
                }
                $find++;
            }
        }

        if ($find == 0) {
            $uri = ".";
        }

        $uri .= "/";

        if ($self == "") {
            $https = "https://" . $host . "/";
            $http = "http://" . $host . "/";
        } else {
            $https = "https://" . $host . "/" . $self . "/";
            $http = "http://" . $host . "/" . $self . "/";
        }

        self::$uri = $uri;
        if (count($free_get) > 1) {
            self::$pure_uri = $uri . $free_get[1];
        } else {
            self::$pure_uri = $uri;
        }
        self::$https = $https;
        self::$http = $http;

        if (server('https')) {
            if (server('https') != 'off') {
                self::$protocol = $https;
            }
        } else {
            self::$protocol = $http;
        }
    }

    public static function restful()
    {
        $uri = wl::dotslash(self::$uri);
        $exp = wl::slashexplode($uri);
        if (count($exp) > 0 && $exp[0] == "restful") {
            self::$restful = true;
            unset($exp[0]);
            $uri = wl::dotslash(join("/", $exp), true);
            self::$uri = $uri;
        }
    }

    public static function getdir($uri, $extra_dir = false)
    {
        $exp = wl::slashexplode($uri);

        $dir = '';

        if ($extra_dir) {
            $dir .= './' . $extra_dir . '/';
        }

        if (count($exp) > 0) {

            $x = 0;

            if ($dir == '') {
                $dir .= wl::dotslash($exp[$x], true);
            } else {
                $dir .= $exp[$x] . '/';
            }

            if (is_directory($dir)) {
                unset($exp[$x]);
            } else {
                if ($dir == '') {
                    $dir .= "./";
                } else {
                    $dir = './' . $extra_dir . '/';
                }
            }

            foreach ($exp as $key => $value) {
                $ant = $dir;
                if ($x == 0) {
                    $dir .= $value;
                } else {
                    $dir .= "/" . $value;
                }
                if (is_directory($dir)) {
                    unset($exp[$key]);
                } else {
                    $dir = $ant;
                    break;
                }
                $x++;
            }

            if (count($exp) == 0) {
                self::$is_standard = true;
                return array(
                    "page" => self::$standard,
                    "parent" => $dir,
                    "params" => false
                );
            } else {
                $exp = wl::resetarray($exp);
                $page = $exp[0];
                unset($exp[0]);
                $params = array();
                foreach ($exp as $key => $value) {
                    array_push($params, $value);
                }
                return array(
                    "page" => $page,
                    "parent" => $dir,
                    "params" => $params
                );
            }
        }
        self::$is_standard = true;
        return array(
            "page" => self::$standard,
            "parent" => "./",
            "params" => false
        );
    }

    public static function return_path_route()
    {
        $parent = self::$parent;
        $router = self::$router;
        $page = self::$page;
        if (is_null($router)) {
            $router = 1;
        }
        switch ($router) {
            case 1:
                return array(
                    "file" => $parent . "routes/" . $page . ".php",
                    "dir" => $parent . "routes/",
                    "page" => $page
                );
                break;

            case 2:
                return array(
                    "file" => "./routes/" . $page . ".php",
                    "dir" => "./routes/",
                    "page" => $page
                );
                break;

            case 3:
                return array(
                    "file" => $parent . "routes/routes.php",
                    "dir" => $parent . "routes/",
                    "page" => "routes"
                );
                break;

            default:
                return array(
                    "file" => "./routes/routes.php",
                    "dir" => "./routes/",
                    "page" => "routes"
                );
                break;
        }
    }

    /**
     * Example:
     * <code>
     * Winged::addRoute('./init/', array(
     *      "index" => "real_path_to_my_view.php"
     * ));
     *
     * or...
     *
     * Winged::addRoute('./init/pattern_rule_for_this_parameter', array(
     *      "index" => "real_path_to_my_view.php"
     *       new Parameter("my_parameter", "./other_file_include.php"),
     * ));
     * </code>
     * @access public
     * @param string $index path or math to search in url.
     * @param array $route is an array with all parameters.
     * @return void
     */
    public static function addroute($index, $route)
    {
        self::$rewrite_obj->addroute($index, $route);
    }

    public static function addrest($index, $rest)
    {
        self::$restful_obj->addrest($index, $rest);
    }

    public static function error($str)
    {
        echo($str);
        exit;
    }

    public static function obStart()
    {
        if (!self::$ob_buffer) {
            if (ob_start('mb_output_handler')) {
                self::$ob_buffer = true;
            }
        }
    }

    public static function obGetFinish()
    {
        if (self::$ob_buffer && ob_get_length() > 0) {
            $content = ob_get_contents();
            ob_flush();
            self::$ob_buffer = false;
            return $content;
        }
        return false;
    }

    public static function obGet()
    {
        if (self::$ob_buffer && ob_get_length() > 0) {
            $content = ob_get_contents();;
            return $content;
        }
        return false;
    }

    public static function obReset()
    {
        self::obFinish();
        self::obStart();
    }

    public static function obShowFinish()
    {
        if (self::$ob_buffer && ob_get_length() > 0) {
            ob_end_flush();
            self::$ob_buffer = false;
        }
    }

    public static function obFinish()
    {
        if (self::$ob_buffer && ob_get_length() > 0) {
            ob_end_clean();
            self::$ob_buffer = false;
        }
    }


    public static function push_warning($error_in, $str, $add_backtrace = false)
    {
        $ignore = array("Winged", "Rewrite", "Restful", "include", "include_once", "require", "require_once");
        $backtrace = debug_backtrace();
        $simple_backtrace = array();
        foreach ($backtrace as $key => $value) {
            $valid = true;
            if (isset($value["class"])) {
                if (in_array($value["class"], $ignore)) {
                    $valid = false;
                }
            }
            if (isset($value["function"])) {
                if (in_array($value["function"], $ignore)) {
                    $valid = false;
                }
            }
            if ($valid) {
                $simple_backtrace[] = $value;
            }
        }

        $function = false;
        $class = false;

        if (isset($simple_backtrace[0])) {
            if (isset($simple_backtrace[0]["function"])) {
                $function = $simple_backtrace[0]["function"];
            }
            if (isset($simple_backtrace[0]["class"])) {
                $class = $simple_backtrace[0]["class"];
            }
            if ($add_backtrace) {
                self::$warnings[] = array("error_in" => $error_in, "error_description" => $str, "call_in_file" => $simple_backtrace[0]["file"], "on_line" => $simple_backtrace[0]["line"], "class" => $class, "function" => $function, "real_backtrace" => $backtrace, "simple_backtrace" => $simple_backtrace);
            } else {
                self::$warnings[] = array("error_in" => $error_in, "error_description" => $str, "call_in_file" => $simple_backtrace[0]["file"], "on_line" => $simple_backtrace[0]["line"], "class" => $class, "function" => $function);
            }
        } else {
            if ($add_backtrace) {
                self::$warnings[] = array("error_in" => $error_in, "error_description" => $str, "call_in_file" => null, "on_line" => null, "class" => $class, "function" => $function, "real_backtrace" => $backtrace, "simple_backtrace" => $simple_backtrace);
            } else {
                self::$warnings[] = array("error_in" => $error_in, "error_description" => $str, "call_in_file" => null, "on_line" => null, "class" => $class, "function" => $function);
            }
        }
        return end(self::$warnings);
    }

    public static function clear_warnings()
    {
        self::$warnings = array();
    }

    public static function clear_errors()
    {
        self::$errors = array();
    }

    public static function fatalError($error_in, $str, $add_backtrace = false)
    {
        Winged::clear_warnings();
        Winged::clear_errors();
        Winged::push_warning($error_in, $str, $add_backtrace);
        Winged::convert_warnings_into_erros();
        Winged::get_errors(__LINE__, __FILE__);
    }

    public static function push_error($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (!in_array($errno, self::$ignore_errors)) {
            self::$errors[] = array(
                "error_type" => $errno,
                "error_str" => $errstr,
                "error_file" => $errfile,
                "error_line" => $errline,
                "context" => $errcontext
            );
        }
    }

    public static function get_errors($line, $file, $exit = true)
    {
        if (self::error_exists()) {
            Winged::obReset();
            ?>
            <html>
            <head>
                <base href="<?= Winged::$protocol . "winged/" ?>">
                <title>Trace error</title>
                <meta charset="utf-8"/>
                <link href="classes/error_files/winged.error.css" rel="stylesheet" type="text/css"/>
                <link href="classes/error_files/font-awesome.min.css" rel="stylesheet" type="text/css"/>
                <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=1"/>
                <link rel="icon" href="assets/img/fav.png"/>
            </head>
            <body>
            <div class="windows">
                <div class="close">
                    <i class="fa fa-times"></i>
                </div>
                <div class="content">

                </div>
            </div>
            <table>
                <thead>
                <tr>
                    <td>Error type</td>
                    <td>Error description</td>
                    <td>Called in line</td>
                    <td>Error in file</td>
                    <td>Error on line</td>
                    <td>Error context</td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach (self::$errors as $key => $error) {
                    ?>
                    <tr>
                        <td><?= $error["error_type"] ?></td>
                        <td class="can-select"><?= $error["error_str"] ?></td>
                        <td><?= $file . ' : ' . $line ?></td>
                        <td><?= $error["error_file"] ?></td>
                        <td><?= $error["error_line"] ?></td>
                        <?php
                        if (!empty($error["context"])) {
                            ?>
                            <td>
                                <span class="view">Click to view context</span>
                                <pre class="no-display">
                                    <?= print_r($error["context"]) ?>
                                </pre>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>
                                <span>Context no exists</span>
                            </td>
                            <?php
                        }
                        ?>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            </body>
            <script src="classes/error_files/jquery.js"></script>
            <script>
                $(function () {
                    $(".view").closest("td").on("click", function () {
                        var $self = $(this);
                        var pre = $self.find(".no-display");
                        var value = pre.text();
                        var _window = $(".windows");
                        _window.find(".content").html("<pre>" + htmlspecialchars(value) + "</pre>");
                        _window.fadeIn(200);
                        $("body, html").animate({
                            scrollTop: _window.offset().top,
                        }, 500);
                    });
                    $(".close").on("click", function () {
                        var _window = $(".windows");
                        _window.fadeOut(200);
                    });
                });
                function htmlspecialchars(text) {
                    var map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };

                    return text.replace(/[&<>"']/g, function (m) {
                        return map[m];
                    });
                }
            </script>
            </html>
            <?php
            if ($exit) {
                exit;
            }
        }
    }

    public function setDefault404()
    {
        self::$restful_obj->setDefault404();
    }

    public static function post()
    {
        return $_POST;
    }

    public static function get()
    {
        return $_GET;
    }

    public static function error_exists()
    {
        if (is_array(self::$errors) && count(self::$errors)) {
            return true;
        }
        return false;
    }

    public static function warning_exists()
    {
        if (is_array(self::$warnings) && count(self::$warnings)) {
            return true;
        }
        return false;
    }

    public static function get_warnings()
    {
        return self::$warnings;
    }

    public static function convert_warnings_into_erros()
    {
        foreach (self::$warnings as $key => $warning) {
            if (array_key_exists("real_backtrace", $warning)) {
                winged_error_handler("8", $warning["error_description"], "(logic error)", "in class : " . __LINE__, $warning["real_backtrace"]);
            } else {
                winged_error_handler("8", $warning["error_description"], "(logic error)", "in class : " . __LINE__);
            }
        }
    }

    public static function initialJs()
    {
        return '<script>
                    window.protocol = "' . Winged::$protocol . '"; 
                    window.page_surname = "' . Winged::$page_surname . '"; 
                    window.uri = "' . Winged::$uri . '"; 
                    window.controller_params = JSON.parse(\'' . json_encode(Winged::$controller_params) . '\'); 
                    window.controller_action = "' . Winged::$controller_action . '";                
                </script>';
    }

}

if (file_exists('./winged.globals.php')) {
    include_once './winged.globals.php';
}