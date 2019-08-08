<?php

namespace Winged\Utils;

/**
 * Class WingedLib
 */
class WingedLib
{

    /**
     * clear document root
     *
     * @return mixed
     */
    public static function clearDocumentRoot()
    {
        $exp = explode(':/', DOCUMENT_ROOT);
        if (count7($exp) === 2) {
            return $exp[1];
        }
        return $exp[0];
    }

    /**
     * normalize path
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath($path = '')
    {
        if ($path === '') {
            return './';
        }
        if (strlen($path) > 0) {
            $path = trim(str_replace("\\", "/", $path));
            $path = trim(str_replace("/./", "/", $path));
            if ($path[0] === '/') {
                $path = '.' . $path;
            }
            if ($path[strlen($path) - 1] != '/') {
                $path .= '/';
            }
            if ((!is_int(stripos($path, './'))) || (is_int(stripos($path, './')) && ((int)stripos($path, './')) > 0)) {
                $path = './' . $path;
            }
            $path = trim(str_replace("/./", "/", $path));
            return trim($path);
        }
        return './';
    }

    /**
     * explode path using one / (right bar)
     *
     * @param string $path
     *
     * @return array|bool
     */
    public static function explodePath($path = '')
    {
        $path = self::clearPath($path);
        if ($path) {
            $path = explode('/', $path);
        }
        return $path;
    }

    /**
     * clear path
     *
     * @param $path
     *
     * @return bool|string
     */
    public static function clearPath($path)
    {
        $path = self::normalizePath($path);
        $path = str_replace('./', '', $path);
        if (strlen($path) > 0) {
            $path = substr_replace($path, '', strlen($path) - 1, 1);
            return trim($path);
        }
        return false;
    }

    /**
     * convert left slashs to right slashs
     *
     * @param $str
     *
     * @return string
     */
    public static function convertslash($str)
    {
        return trim(str_replace("\\", "/", $str));
    }

    /**
     * alias for $_SERVER, ignore case sentive and if key not exists in $_SERVER returns false
     *
     * @param $key
     *
     * @return bool
     */
    public static function server($key)
    {
        $ukey = strtoupper($key);
        $server = $_SERVER;
        if (array_key_exists($ukey, $server)) {
            return $server[$ukey];
        }
        return false;
    }

    /**
     * normalize an URL
     *
     * @param string $url
     *
     * @return mixed
     */
    public static function normalizeUrl($url = '')
    {
        return str_replace(['http://', 'https://', '//', 'http:~~', 'https:~~', '??'], ['http:~~', 'https:~~', '/', 'http://', 'https://', '?'], $url);
    }

}