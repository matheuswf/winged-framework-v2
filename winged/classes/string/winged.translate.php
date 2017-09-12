<?php

class CoreTranslate {

    public static $file, $show, $last, $lang;

    public static function init($file, $lang, $show = 1) {
        if (file_exists($file)) {
            self::$file = $file;
            self::$show = $show;
            self::$lang = $lang;
        } else {
            Winged::error("File not found in {$file}");
        }
    }

    public static function setShow($show) {
        self::$show = $show;
    }

    public static function show($key, $arr = array()) {
        $lang = self::$lang;
        require_once self::$file;
        if (array_key_exists($lang, $text)) {
            if (array_key_exists($key, $text[$lang])) {
                if (self::$show == 1) {
                    self::$last = $text[$lang][$key];
                    echo self::pf($text[$lang][$key], $arr);
                    return 0;
                } else {
                    self::$last = $text[$lang][$key];
                    return self::pf($text[$lang][$key], $arr);
                }
            } else {
                Winged::error("This key not set in language {$lang}. Key: {$key}");
            }
        } else {
            Winged::error("This language not set in array. Lang: {$lang}");
        }
    }

    public static function pf($str, $arr) {
        for ($x = 0; $x < count($arr); $x++) {
            $pos = strpos($str, "%s");
            if ($pos !== false) {
                $min = substr($str, 0, $pos);
                $max = substr($str, $pos + 2, strlen($str));
                $str = $min . $arr[$x] . $max;                
            }
        }      
        return $str;
    }
}
