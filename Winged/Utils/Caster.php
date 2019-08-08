<?php

namespace Winged\Utils;

/**
 * Class Caster
 */
class Caster
{

    /**
     * cast to int
     *
     * @param bool|string|int $value
     *
     * @return bool|int
     */
    public static function toInt($value = false)
    {
        if (!is_scalar($value)) {
            return false;
        }
        $float = floatval($value);
        $int = intval($value);
        $string = "" . $value . "";
        $floatString = "" . $float . "";
        $intString = "" . $int . "";
        if ($string === $floatString || $intString === $string) {
            return $int;
        }
        return false;
    }

    /**
     * cast to float
     *
     * @param bool|string|int $value
     *
     * @return bool|float
     */
    public static function toFloat($value = false)
    {
        if (!is_scalar($value)) {
            return false;
        }
        $dotPos = strrpos($value, '.');
        $commaPos = strrpos($value, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $value));
        }

        return floatval(
            preg_replace("/[^0-9]/", "", substr($value, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($value, $sep + 1, strlen($value)))
        );
    }

    /**
     * cast to float ou int
     *
     * @param bool $value
     *
     * @return bool|float|int
     */
    public static function toNumber($value = false)
    {
        $float = self::toFloat($value);
        $int = self::toInt($value);
        if ($float == $int) {
            return $int;
        } else {
            return $float;
        }
    }

    /**
     * cast to float
     *
     * @param bool $value
     *
     * @return bool|float
     */
    public static function toString($value = false)
    {
        if (!is_scalar($value)) {
            return false;
        }
        return "" . $value . "";
    }

}