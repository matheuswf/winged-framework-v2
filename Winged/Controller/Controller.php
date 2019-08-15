<?php

namespace Winged\Controller;

use Winged\Database\Connections;
use Winged\Directory\Directory;
use Winged\File\File;
use Winged\Frontend\Render;
use Winged\Http\HttpResponseHandler;
use Winged\Route\Route;
use Winged\Utils\WingedLib;
use Winged\App\App;
use \WingedConfig;
use Winged\Error\Error;

/**
 * Class Controller
 *
 * @package Winged\Controller
 */
class Controller extends Render
{
    public static $CONTROLLERS_PATH = './controllers/';
    public static $MODELS_PATH = './models/';
    public static $VIEWS_PATH = './views/';

    private $controller_path = false;
    private $controller_name = false;
    private $action_name = false;
    private $query_params = [];
    private $method_args = [];

    /**
     * @var null | Route
     */
    private $route = null;


    /**
     * Controller constructor.
     *
     * @param null $route
     */
    public function __construct($route = null)
    {
        $this->route = $route;
        parent::__construct();
    }

    /**
     * @param $route null | Route
     *
     * @return null | Route
     */
    public function route($route = null)
    {
        if($route && is_object($route)){
            if(get_class($route) === 'Winged\Route\Route'){
                $this->route = $route;
            }
        }
        return $this->route;
    }

    /**
     * create property dynamic inside current controller
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function dynamic($key, $value)
    {
        $this->{$key} = $value;
        return $this;
    }


    /**
     * get query string from $_GET + opction $push param
     *
     * @param array $push
     *
     * @return string
     */
    public function getQueryStringConcat($push = [])
    {
        $param = '';
        $fisrt = true;
        foreach ($this->method_args as $key => $value) {
            if ($fisrt) {
                $fisrt = false;
                if (array_key_exists($key, $push)) {
                    $param .= $key . '=' . $push[$key];
                    unset($push[$key]);
                } else {
                    $param .= $key . '=' . $value;
                }
            } else {
                if (array_key_exists($key, $push)) {
                    $param .= '&' . $key . '=' . $push[$key];
                    unset($push[$key]);
                } else {
                    $param .= '&' . $key . '=' . $value;
                }
            }
        }
        $fisrt = false;
        foreach ($push as $key => $value) {
            if ($fisrt) {
                $fisrt = false;
                $param .= $key . '=' . $value;
            } else {
                $param .= '&' . $key . '=' . $value;
            }
        }
        return $param;
    }

      /**
     * redirect to any location, full URL string is required
     *
     * @param string $path
     */
    public function redirectOnly($path = '')
    {
        header('Location: ' . $path);
    }

    /**
     * redirect to an path
     *
     * @param string $path
     * @param bool   $keep_args
     */
    public function redirectTo($path = '', $keep_args = true)
    {
        $args_path = explode('?', $path);
        $path = $args_path[0];
        $args = explode('?', server('request_uri'));
        $join = [];
        if (count7($args) >= 2 && $keep_args) {
            if (count7($args_path) >= 2) {
                $args = explode('&', end($args));
                $args_path = explode('&', end($args_path));
                foreach ($args_path as $arg) {
                    $from_redi = explode('=', $arg);
                    $key_redi = array_shift($from_redi);
                    $from_redi = [$key_redi => (count7($from_redi) > 1 ? implode('=', $from_redi) : end($from_redi))];
                    foreach ($args as $varg) {
                        $from_url = explode('=', $varg);
                        $key_url = array_shift($from_url);
                        $from_url = [$key_url => (count7($from_url) > 1 ? implode('=', $from_url) : end($from_url))];
                        if ($key_redi == $key_url) {
                            $join[$key_redi] = $from_redi[$key_redi];
                        } else {
                            $join[$key_url] = $from_url[$key_url];
                        }
                    }
                }
                $args = '';
                foreach ($join as $key => $value) {
                    $args .= $key . '=' . $value;
                }
            } else {
                $args = '?' . array_pop($args);
            }
        } else {
            if (count7($args_path) >= 2) {
                $args = end($args_path);
            } else {
                $args = '';
            }
        }
        if ($path != '') {
            if (endstr($path) != '/') {
                $path .= '/';
            }
        }
        if (trim($args) != '') {
            $args = '?' . $args;
        }
        if (WingedConfig::$config->PARENT_FOLDER_MVC) {
            $parent = WingedLib::clearPath(App::$parent);
            if ($parent == '') {
                header('Location: ' . App::$protocol . $path . $args);
            } else {
                header('Location: ' . App::$protocol . $parent . '/' . $path . $args);
            }
        } else {
            header('Location: ' . App::$protocol . $path . $args);
        }
        App::_exit();
    }

    /**
     * set nicknames in uri
     * Ex: /users/edit/1
     * {controller}/{action}/1
     * $nicks = ['id']
     * {controller}/{action}/{id}
     *
     * @param array $nicks
     */
    public function setNicknamesToUri($nicks = [])
    {
        $narr = [];
        if (App::$controller_params == false) {
            App::$controller_params = [];
        }
        if (count7($nicks) > count7(App::$controller_params)) {
            for ($x = 0; $x < count7($nicks); $x++) {
                if (array_key_exists($x, App::$controller_params)) {
                    $narr[$nicks[$x]] = App::$controller_params[$x];
                } else {
                    $narr[$nicks[$x]] = null;
                }
            }
        } else {
            for ($x = 0; $x < count7($nicks); $x++) {
                $narr[$nicks[$x]] = App::$controller_params[$x];
            }
        }
        App::$controller_params = $narr;
    }

    /**
     * copy informations from main controler locate in Winged to new controller
     */
    public function copy()
    {
        if (App::$controller !== null) {
            App::$controller->getGetArgs();
            $this->controller_path = App::$controller->controller_path;
            $this->controller_name = App::$controller->controller_name;
            $this->query_params = App::$controller->query_params;
            $this->method_args = App::$controller->method_args;
            $this->action_name = App::$controller->action_name;
        }
    }

    /**
     * get specific
     *
     * @param $key
     *
     * @return bool|mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->method_args)) {
            return $this->method_args[$key];
        }
        return false;
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function params($key)
    {
        if (array_key_exists($key, $this->query_params)) {
            return $this->query_params[$key];
        }
        return false;
    }

    /**
     * render view/controller response as html, add head tag with js and css files, and raw headers
     *
     * @param string $path
     * @param array  $vars
     *
     * @return bool
     */
    public function html($path, $vars = [])
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            try {
                $this->activeMinify();
            } catch (\Exception $exception) {
                trigger_error($exception->getMessage(), E_USER_ERROR);
            }
            if (is_string(WingedConfig::$config->HEAD_CONTENT_PATH)) {
                $this->appendAbstractHead('__first_head_content___', WingedConfig::$config->HEAD_CONTENT_PATH);
            }
            $this->configureAssets($content);
            $this->compactHtml($content);
            $this->reconfigurePaths($content);
            return $this->channelingRender($content, 'html');
        }
        return false;
    }

    /**
     * return view path has file path
     *
     * @param $path
     *
     * @return string
     */
    private function view($path)
    {
        $path .= '.php';
        return self::$VIEWS_PATH . $path;
    }

    /**
     * render any file with extensions *.html, *.json, *.php, *.yaml and *.json
     *
     * @param string $path
     * @param array  $vars
     *
     * @return bool
     */
    public function file($path, $vars = [])
    {
        $content = $this->_render($path, $vars);
        $file = new File($path, false);
        if ($content && $this->checkCalls()) {
            return $this->channelingRender($content, ($file->getExtension() === 'php' ? 'html' : $file->getExtension()));
        }
        return false;
    }

    /**
     * render view/controller response as html without any included html
     *
     * @param string $path
     * @param array  $vars
     * @param bool   $return
     *
     * @return bool|string
     */
    public function partial($path, $vars = [], $return = false)
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            if ($return) {
                return $content;
            }
            return $this->channelingRender($content, 'html');
        }
        return false;
    }

    /**
     * render view/controller response as json
     *
     * @param string $path
     * @param array  $vars
     * @param bool   $return
     *
     * @return bool|string
     */
    public function json($path, $vars = [], $return = false)
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            if ($return) {
                return $content;
            }
            return $this->channelingRender($content, 'json');
        }
        return false;
    }

    /**
     * channel all final render calls to this function, check if an error exists and if not, dispatch http response with content
     *
     * @param string $content
     * @param string $type
     *
     * @return bool
     */
    private function channelingRender($content, $type = 'html')
    {
        if (Error::exists()) {
            Error::display();
        }
        $response = new HttpResponseHandler();
        switch ($type) {
            case 'html':
                $response->dispatchHtml($content, false);
                return true;
                break;
            case 'json':
                $response->dispatchJson($content, false);
                return true;
                break;
            case 'xml':
                $response->dispatchXml($content, false);
                return true;
                break;
        }
        return false;
    }

}