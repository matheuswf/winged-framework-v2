<?php

use Winged\WingedConfigDefaults;

/**
 * Customize your application here
 *
 * Class WingedConfig
 */
class WingedConfig extends WingedConfigDefaults
{
    /**
     * @var null | WingedConfig
     * no delete this property
     */
    public static $config = null;
    public $INDEX_ALIAS_URI = "./home/hue";
    public $TIMEZONE = "America/Sao_Paulo";
    public $AUTO_MINIFY = 10;
    public $HTML_LANG = 'pt-BR';
    public $INCLUDES = [
    ];
}