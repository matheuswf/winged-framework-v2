<?php

namespace Winged\App;

if (!function_exists('getallheaders')) {
    /**
     * if this native function no exists, i can create it for you
     *
     * @return array
     */
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Class Request
 *
 * @package Winged\App
 */
class Request
{

    public $headers = [];
    public $accept = [];

    public function __construct()
    {
        $this->headers = \getallheaders();
        $this->accept = isset($this->headers['Accept']) ? $this->headers['Accept'] : 'text/html';
        $this->accept = explode(',', $this->accept);
        $newAccept = [];
        foreach ($this->accept as $key => $accepted) {
            $theKey = $accepted;
            $theValue = $accepted;
            $explodeAccept = explode(';', $accepted);
            if (count($explodeAccept) > 1) {
                $theKey = $explodeAccept[0];
                $theValue = explode('&', $explodeAccept[1]);
                foreach ($theValue as $_key => $value) {
                    $exp = explode('=', $value);
                    if (count($exp) > 1) {
                        $theValue[$_key] = [$exp[0] => $exp[1]];
                    } else {
                        $theValue[$_key][] = $exp[0];
                    }
                }
            }
            $newAccept[$theKey] = $theValue;
        }
        $this->accept = $newAccept;
        if (empty($this->accept)) {
            $this->accept = [
                'application/json' => 'application/json'
            ];
        }
    }

    /**
     * @param string $mimeType
     * @param bool   $checkAll
     *
     * @return bool
     */
    public function isAcceptableType($mimeType = 'appliction/json', $checkAll = false)
    {
        $allChecked = true;
        if (!is_array($mimeType)) {
            $mimeType = [$mimeType];
        }
        if (is_array($mimeType)) {
            foreach ($mimeType as $type) {
                if ($checkAll) {
                    if (!array_key_exists($type, $this->accept)) {
                        $allChecked = false;
                    }
                } else {
                    if (array_key_exists($type, $this->accept)) {
                        return true;
                    }
                }
            }
            if ($allChecked) {
                return true;
            }
            return false;
        }
    }

    /**
     * @return string
     */
    public function getAcceptablePriority()
    {
        $keys = array_keys($this->accept);
        return $keys[0];
    }

}