<?php

use Winged\Buffer\Buffer;
use Winged\Winged;
use Winged\Database\CurrentDB;

/**
 * return all memory used
 * @return string
 */
function get_memory_usage()
{
    return number_format((memory_get_usage(false) / 1024 / 1024), 2);
}

/**
 * return the max memory usage in the request
 * @return string
 */
function get_memory_peak_usage()
{
    return number_format((memory_get_peak_usage(false) / 1024 / 1024), 2);
}

/**
 * return the int or float value if is a valid number, else return false
 * @param $value
 * @return bool|float|int
 */
function numeric_is($value)
{
    if(is_object($value)){
        return false;
    }
    if (is_array($value)) {
        return false;
    }
    $cp_int = intval($value);
    $cp_flo = floatval($value);
    $str_val = strval($value);
    $cp_int_str = strval($cp_int);
    $cp_flo_str = strval($cp_flo);
    if ($str_val == $cp_flo_str) {
        return (float)$cp_flo;
    }
    if ($str_val == $cp_int_str) {
        return (int)$cp_int;
    }
    return false;
}

/**
 * if is array and key exists in array, return value from index, else return false
 * util use inside if statements
 * @param $key
 * @param $haystack
 * @return bool|mixed
 */
function array_key_exists_check($key, $haystack)
{
    if (is_array($haystack)) {
        if (array_key_exists($key, $haystack)) {
            return $haystack[$key];
        }
    }
    return false;
}

/**
 * if is object and property exists in object, return value from property, else return false
 * util use inside if statements
 * @param $property
 * @param $object
 * @return bool|mixed
 */
function object_key_exists_check($property, $object)
{
    if (is_object($object)) {
        if (property_exists(get_class($object), $property)) {
            return $object->{$property};
        }
    }
    return false;
}

/**
 * alias for $_SERVER, ignore case sentive and if key not exists in $_SERVER returns false
 * @param $key
 * @return bool
 */
function server($key)
{
    $ukey = strtoupper($key);
    $server = $_SERVER;
    if (array_key_exists($ukey, $server)) {
        return $server[$ukey];
    }
    return false;
}

/**
 * return true if key exists inside $_SERVER or false if not exists
 * @param $key
 * @return bool
 */
function serverset($key)
{
    $ukey = strtoupper($key);
    $server = $_SERVER;
    if (array_key_exists($ukey, $server)) {
        return true;
    }
    return false;
}

/**
 * return true if key exists inside $_POST or false if not exists
 * @param $key
 * @return bool
 */
function postset($key)
{
    if (array_key_exists($key, $_POST)) {
        return true;
    }
    return false;
}

/**
 * return value from parsed $_POST if key exists or false if not exists
 * @param $key
 * @return boolean | array | string
 */
function post($key)
{
    if (array_key_exists($key, $_POST)) {
        if (numeric_is($_POST[$key])) {
            return numeric_is($_POST[$key]);
        }
        return $_POST[$key];
    }
    return false;
}

/**
 * return value from original $_POST if key exists or false if not exists
 * @param $key
 * @return boolean | array | string
 */
function unpost($key)
{
    global $_OPOST;
    if (array_key_exists($key, $_OPOST)) {
        if (numeric_is($_OPOST[$key])) {
            return numeric_is($_OPOST[$key]);
        }
        return $_OPOST[$key];
    }
    return false;
}

/**
 * return true if key exists inside $_GET or false if not exists
 * @param $key
 * @return bool
 */
function getset($key)
{
    if (array_key_exists($key, $_GET)) {
        return true;
    }
    return false;
}

/**
 * return value from parsed $_GET if key exists or false if not exists
 * @param $key
 * @return boolean | array | string
 */
function get($key)
{
    if (array_key_exists($key, $_GET)) {
        if (numeric_is($_GET[$key])) {
            return numeric_is($_GET[$key]);
        }
        return $_GET[$key];
    }
    return false;
}

/**
 * return value from original $_GET if key exists or false if not exists
 * @param $key
 * @return boolean | array | string
 */
function unget($key)
{
    global $_OGET;
    if (array_key_exists($key, $_OGET)) {
        if (numeric_is($_OGET[$key])) {
            return numeric_is($_OGET[$key]);
        }
        return $_OGET[$key];
    }
    return false;
}

/**
 * return true if $_SERVER['REQUEST_METHOD'] matchs with $method_name
 * @param $method_name
 * @return bool
 */
function method($method_name)
{
    if (strtolower($_SERVER["REQUEST_METHOD"]) == strtolower($method_name)) {
        return true;
    }
    return false;
}

/**
 * cut the string from the beginning to the size limit only if it is necessary to make the cut
 * @param string $str
 * @param int $from
 * @param int $length
 * @param string $append
 * @return string
 */
function substr_if_need($str = '', $from = 0, $length = 0, $append = '')
{
    if ($length == null) {
        $length = strlen($str) - 1;
    }
    if (strlen($str) > $from && strlen($str) > $length) {
        if ($length == 0) {
            $length = $from;
            $from = 0;
            return substr($str, $from, $length) . $append;
        } else {
            return substr($str, $from, $length) . $append;
        }
    }
    return $str;
}

/**
 * return true if all keys exists in array
 * @param array $keys
 * @param array $array
 * @return bool
 */
function check_all_keys($keys = [], $array = [])
{
    $exists = true;
    if (!empty($keys) && !empty($array)) {
        foreach ($keys as $key) {
            if (is_string($key) || is_int($key)) {
                if (!array_key_exists($key, $array)) {
                    $exists = false;
                }
            } else {
                $exists = false;
            }
        }
        return $exists;
    }
    return false;
}

/**
 * remove all index from array by values array and return new array
 * @param array $values
 * @param array $array
 * @return array
 */
function remove_key_from_array_by_value($values = [], $array = [])
{
    $narr = [];
    if (!empty($values) && !empty($array)) {
        foreach ($array as $value) {
            if (!in_array($value, $values)) {
                $narr[] = $value;
            }
        }
    }
    return $narr;
}

/**
 * @param array $needle
 * @param array $array
 * @return int|string
 */
function get_key_by_value($needle = [], $array = [])
{
    if (is_string($needle)) {
        foreach ($array as $key => $value) {
            if ($value == $needle) {
                return $key;
            }
        }
    } else {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $condition_count = 0;
                foreach ($value as $ikey => $ivalue) {
                    if (array_key_exists($ikey, $needle) && $needle[$ikey] == $ivalue) {
                        $condition_count++;
                    }
                }
                if ($condition_count == count($value)) {
                    return $key;
                }
            }
        }
    }
}

/**
 * convert array into object recursive
 * @param array $arg
 * @return object | null | array | string | int | bool
 */
function recursive_object($arg)
{
    if (is_array($arg)) {
        $arg = (object)$arg;
    } else {
        return $arg;
    }
    foreach ($arg as $key => $value) {
        if (is_array($value)) {
            $value = recursive_object($value);
            $arg->{$key} = $value;
        } else {
            $arg->{$key} = $value;
        }
    }
    return $arg;
}

/**
 * return key of array by value in key
 * @param null $needle
 * @param array $array
 * @return mixed|null
 */
function get_value_by_key($needle = null, $array = [])
{
    if (array_key_exists($needle, $array)) {
        return $array[$needle];
    }
    return null;
}

if (!function_exists('array_column')) {
    /**
     * if this native function no exists i can create it for you
     *
     * @param array $input
     * @param       $columnKey
     * @param null  $indexKey
     *
     * @return array|bool
     */
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $array = [];
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is post
 * @return bool
 */
function is_post()
{
    return method('post');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is get
 * @return bool
 */
function is_get()
{
    return method('get');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is delete
 * @return bool
 */
function is_delete()
{
    return method('delete');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is post
 * @return bool
 */
function is_put()
{
    return method('put');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is update
 * @return bool
 */
function is_update()
{
    return method('update');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is patch
 * @return bool
 */
function is_patch()
{
    return method('patch');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is options
 * @return bool
 */
function is_options()
{
    return method('options');
}

/**
 * return true if $_SERVER["REQUEST_METHOD"] is uri
 * @return bool
 */
function uri($index)
{
    if (Winged::$params == false) {
        Winged::$params = [];
    }
    if (Winged::$controller_params == false) {
        Winged::$controller_params = [];
    }
    if (array_key_exists($index, Winged::$params)) {
        return no_injection(Winged::$params[$index]);
    }

    if (array_key_exists($index, Winged::$controller_params)) {
        return no_injection(Winged::$controller_params[$index]);
    }
    return false;
}

/**
 * return true if path is a directory
 * @return bool
 */
function is_directory($path = './')
{
    if (is_dir($path)) {
        clearstatcache();
        return true;
    }
    return false;
}

/**
 * return true if WingedConfig::$config->DEV is true
 * @return bool
 */
function is_dev()
{
    if (WingedConfig::$config->DEV != null && is_bool(WingedConfig::$config->DEV)) {
        return WingedConfig::$config->DEV;
    }
}

/**
 * convert string into ancci numbers separated by dot
 * @param $str
 * @return string
 */
function ancci_conv($str)
{
    $nums = "";
    for ($x = 0; $x < strlen($str); $x++) {
        $nums .= "." . ord($str[$x]);
    }
    return $nums;
}

/**
 * apply anti-mysql injection to an array
 * @param $array array
 * @return array
 */
function no_injection_array($array)
{
    $each = [];
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (gettype($value) == "array") {
                $each[$key] = no_injection_array($value);
            } else {
                $each[$key] = no_injection($value);
            }
        }
    }
    return $each;
}

/**
 * apply anti-mysql injection to an string
 * @param $str string
 * @return mixed
 */
function no_injection($str)
{
    if (WingedConfig::$config->db()->STD_DB_CLASS === IS_MYSQLI) {
        return CurrentDB::$current->db->real_escape_string($str);
    }
    return $str;
}

/**
 * trade any new line for all systems to html5 tag <br>
 * @param $str
 * @return mixed
 */
function nltobr($str)
{
    return str_replace(["\r\n", "\n", "\r", '\r\n', '\n', '\r'], "<br />", $str);
}

/**
 * trade any <br> tag to correct new line
 * @param $str
 * @return mixed
 */
function brtonl($str)
{
    return str_ireplace(["<br />", "<br>", "<br/>"], "\r\n", $str);
}

/**
 * @param $array
 * @param bool $die
 */
function pre($array, $die = false)
{
    if (is_array($array) && empty($array)) {
        $array = 'Empty array';
    } else if (is_null($array)) {
        $array = 'Null argument';
    } else if (is_bool($array) && $array === true) {
        $array = 'True value argument';
    } else if (is_bool($array) && $array === false) {
        $array = 'False value argument';
    } else if (is_int($array)) {
        $array .= ' : INT';
    } else if (is_string($array)) {
        $array .= ' : STRING';
    }
    echo "<pre style='padding: 20px; background: #fefefe; font-family: monospace; font-size: 14px; border: 1px solid #494949; margin: 10px 5px; border-radius: 2px; word-wrap: break-word'>";
    print_r($array);
    echo "</pre>";
    if ($die) {
        Winged::_exit();
    }
}

/**
 * @param array $array
 */
function pre_clear_buffer_die($array = [])
{
    if (is_array($array) && empty($array)) {
        $array = 'Empty array';
    } else if (is_null($array)) {
        $array = 'Null argument';
    } else if (is_bool($array) && $array === true) {
        $array = 'True value argument';
    } else if (is_bool($array) && $array === false) {
        $array = 'False value argument';
    } else if (is_int($array)) {
        $array .= ' : INT';
    } else if (is_string($array)) {
        $array .= ' : STRING';
    }
    Buffer::reset();
    ?>
    <html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
    <?php
    echo "<pre style='padding: 20px; background: #fefefe; font-family: monospace; font-size: 14px; border: 1px solid #494949; margin: 10px 5px; border-radius: 2px; word-wrap: break-word'>";
    print_r($array);
    echo "</pre>";
    ?>
    </body>
    </html>
    <?php
    Buffer::flush();
    Winged::_exit();
}

$printed_pre = [];
$beggin_pre = false;

/**
 * begin manual debugger array
 */
function begin_pre()
{
    global $beggin_pre;
    $beggin_pre = true;
}

/**
 * reset manual debugger array
 */
function reset_pre()
{
    global $printed_pre;
    $printed_pre = [];
}

/**
 * register output for debugger array
 * @param $array
 * @param bool $force_beggin
 */
function register_pre($array, $force_beggin = false)
{
    global $printed_pre, $beggin_pre;
    if ($force_beggin) {
        begin_pre();
    }
    if ($beggin_pre) {
        $printed_pre[] = $array;
    }
}

/**
 * free all index in debugger array
 * @param bool $die
 */
function delegate_pre($die = false)
{
    global $printed_pre;
    foreach ($printed_pre as $array) {
        if (is_array($array) && empty($array)) {
            $array = 'Empty array';
        } else if (is_null($array)) {
            $array = 'Null argument';
        } else if (is_bool($array) && $array === true) {
            $array = 'True value argument';
        } else if (is_bool($array) && $array === false) {
            $array = 'False value argument';
        } else if (is_int($array)) {
            $array .= ' : INT';
        } else if (is_string($array)) {
            $array .= ' : STRING';
        }
        echo "<pre style='padding: 20px; background: #fefefe; font-family: monospace; font-size: 14px; border: 1px solid #494949; margin: 10px 5px; border-radius: 2px; word-wrap: break-word'>";
        print_r($array);
        echo "</pre>";
    }
    if ($die) {
        Winged::_exit();
        exit;
    }
}

/**
 * free all index in debugger array and stop execution
 * @param bool $die
 */
function delegate_pre_clear_buffer_die()
{
    global $printed_pre;
    Buffer::reset();
    ?>
    <html>
    <head>
        <meta charset="utf-8">
    </head>
<body>
    <?php
    if (count7($printed_pre) > 0) {
        foreach ($printed_pre as $array) {
            if (is_array($array) && empty($array)) {
                $array = 'Empty array';
            } else if (is_null($array)) {
                $array = 'Null argument';
            } else if (is_bool($array) && $array === true) {
                $array = 'True value argument';
            } else if (is_bool($array) && $array === false) {
                $array = 'False value argument';
            } else if (is_int($array)) {
                $array .= ' : INT';
            } else if (is_string($array)) {
                $array .= ' : STRING';
            }
            echo "<pre style='padding: 20px; background: #fefefe; font-family: monospace; font-size: 14px; border: 1px solid #494949; margin: 10px 5px; border-radius: 2px; word-wrap: break-word'>";
            print_r($array);
            echo "</pre>";
        }
        ?>
        </body>
        </html>
        <?php
        Buffer::flush();
        Winged::_exit();
        exit;
    }
}

/**
 * echo argument with br
 * @param $arg
 */
function echobr($arg)
{
    echo $arg . "<br>";
}

/**
 * echo argument with new line
 * @param $arg
 */
function echon($arg)
{
    echo $arg . "\n";
}

/**
 * generates a random id of size twelve as default
 * @param int $length
 * @return string
 */
function randid($length = 12)
{
    $id = '';
    $dict = [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];

    for ($x = 0; $x < $length; $x++) {
        $r = rand(0, 25);
        $id .= $dict[$r];
    }
    return $id;
}

/**
 * return first char of string
 * @param $str string
 * @return string | false
 */
function begstr($str)
{
    if (is_string($str)) {
        if (strlen($str) > 0) {
            return $str[0];
        }
    }
    return false;
}

/**
 * replace first char of string
 * @param $str string
 * @param string $replace_with
 */
function begstr_replace(&$str, $replace_with = '')
{
    if (is_string($str)) {
        $str = substr($str, 1, strlen($str) - 1);
        $str = $replace_with . $str;
        $str = trim($str);
    }
}

/**
 * return last char of string
 * @param $str string
 * @param int $length
 * @return string | bool
 */
function endstr($str, $length = 1)
{
    if (is_string($str)) {
        if (strlen($str) - $length > 0) {
            return $str[strlen($str) - $length];
        }
    }
    return false;
}

/**
 * @param $str
 * @param int $length
 * @param string $replace_with
 */
function endstr_replace(&$str, $length = -1, $replace_with = '')
{
    if (is_string($str)) {
        if (strlen($str) - $length > 0) {
            $str = substr_replace($str, $replace_with, strlen($str) - 1, $length);
            $str = trim($str);
        }
    }
}

/**
 * @param array $array
 * @param string $field
 * @param string $id_field
 * @return array
 */
function array2htmlselect($array = [], $field = '', $id_field = '')
{
    $select = [];
    if (!empty($array)) {
        if (is_object($array[0])) {
            foreach ($array as $key => $row) {
                $array[$key] = (array)$row;
            }
        }

        $names = null;

        if (array_key_exists($field, $array[0])) {
            $names = array_column($array, $field);
        }

        $ids = null;

        if (array_key_exists($id_field, $array[0])) {
            $ids = array_column($array, $id_field);
        }

        if ($names && $ids && count($names) == count($ids)) {
            foreach ($names as $key => $value) {
                $select[$ids[$key]] = $value;
            }
        }
        return $select;
    }
    return $select;
}

/**
 * @param $arg
 * @return bool|int
 */
function count7($arg)
{
    return is_array($arg) ? count($arg) : false;
}