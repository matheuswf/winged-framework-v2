<?php

class CoreString
{
    public static function removeAccents($str, $lowerCase = true)
    {
        $nom = array(
            "á", "à", "é", "è", "ó", "ò", "í", "ì", "ú", "ù", "ö", "ü", "ë", "ä", "ï", "ç", "ã", "õ", "ê", "â", "î", "ô", "û", "ñ", "ý", "ÿ", "Á", "À", "É", "È", "Ó", "Ò", "Í", "Ì", "Ú", "Ù", "Ö", "Ü", "Ë", "Ä", "Ï", "Ç", "Ã", "Õ", "Ê", "Â", "Î", "Ô", "Û", "Ñ", "Ý"
        );
        $con = array(
            "a", "a", "e", "e", "o", "o", "i", "i", "u", "u", "o", "u", "e", "a", "i", "c", "a", "o", "e", "a", "i", "o", "u", "n", "y", "y", "A", "A", "E", "E", "O", "O", "I", "I", "U", "U", "O", "U", "E", "A", "I", "C", "A", "O", "E", "A", "I", "O", "U", "N", "Y"
        );
        if ($lowerCase == true) {
            return trim(strtolower(str_replace($nom, $con, $str)));
        } else {
            return trim(str_replace($nom, $con, $str));
        }
    }

    public static function removeWhiteSpaces($str, $lowerCase = true)
    {
        $str = str_replace(" ", "-", $str);
        $nom = array(
            "”", "“", ":", "_", "'", '"', "*", "(", ")", "´", "`", "~", "¨", "¬", "<", ">", ".", ";", ",", "[", "]", "{", "}", "+", "=", "¹", "²", "³", "/", "\\", "?", "!", "@", "#", "$", "%", "&", "º", "ª", "£", "¢", "|"
        );
        if ($lowerCase == true) {
            return trim(strtolower(str_replace($nom, "-", $str)));
        } else {
            return trim(str_replace($nom, "-", $str));
        }
    }

    public static function toUrl($str, $lowerCase = true)
    {
        $str = trim($str);
        $url = trim(self::removeWhiteSpaces(self::removeAccents($str, $lowerCase), $lowerCase));
        $pos = strpos($url, "--");
        while (is_int($pos)) {
            $url = str_replace("--", "-", $url);
            $pos = strpos($url, "--");
        }
        if (endstr($url) == '-') {
            endstr_replace($url);
        }
        if (begstr($url) == '-') {
            begstr_replace($url);
        }
        return trim($url);
    }

}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false)
    {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}