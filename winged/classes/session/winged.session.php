<?php

class CoreSession
{

    private static $inited = false;

    public static function init()
    {
        if (!isset($_SESSION)) {
            session_start();
            self::$inited = true;
        }
    }

    public static function set($key, $value)
    {
        self::init();
        if (!array_key_exists($key, $_SESSION)) {
            $_SESSION[$key] = $value;
            return $value;
        }
        return false;
    }

    public static function update($key, $value)
    {
        self::init();
        if (array_key_exists($key, $_SESSION)) {
            $_SESSION[$key] = $value;
            return $value;
        }
        return false;
    }

    public static function always($key, $value)
    {
        self::init();
        $_SESSION[$key] = $value;
        return $value;
    }

    public static function get($key)
    {
        self::init();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return false;
    }

    public static function remove($key)
    {
        self::init();
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    public static function finish()
    {
        self::init();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        return true;
    }

}

class CoreCookie
{

    private static $memory = [];

    public static function set($key, $value, $days, $path = '/')
    {
        if (!array_key_exists($key, $_COOKIE)) {
            $time = time() + $days * 24 * 60 * 60;
            self::$memory[$key] = ['value' => $value, 'time' => $time, 'path' => '/'];
            setcookie($key, $value, $time, $path);
            return $value;
        }
        return false;
    }

    public static function always($key, $value, $days, $path = '/')
    {
        $time = time() + $days * 24 * 60 * 60;
        self::$memory[$key] = ['value' => $value, 'time' => $time, 'path' => '/'];
        setcookie($key, $value, $time, $path);
        return $value;
    }

    public static function get($key, $all = false)
    {
        if($all && array_key_exists($key, self::$memory)){
            return self::$memory[$key];
        }else{
            if (array_key_exists($key, $_COOKIE)) {
                return $_COOKIE[$key];
            }
            if (array_key_exists($key, self::$memory)) {
                return self::$memory[$key]['value'];
            }
        }
        return false;
    }

    public static function remove($key, $path = '/')
    {
        if (array_key_exists($key, $_COOKIE)) {
            setcookie($key, '', time() - 1000, '');
            setcookie($key, '', time() - 1000, $path);
            if (array_key_exists($key, self::$memory)) {
                unset(self::$memory[$key]);
            }
            return true;
        }
        return false;
    }
}