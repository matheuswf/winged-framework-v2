<?php

class WingedDOMElement extends WingedDOM
{
    public $tagName = null;
    public $text = null;
    public $id = null;
    public $class = null;
    public $attributes = null;
    public $origText = null;

    /**
     * @var $parent WingedDOMElement
     */
    public $parent = null;
    /**
     * @var $next_brother WingedDOMElement
     */
    public $next_brother = null;
    /**
     * @var $prev_brother WingedDOMElement
     */
    public $prev_brother = null;
    /**
     * @var $prev_brother WingedDOMElement
     */
    public $first_child = null;
    /**
     * @var $last_child WingedDOMElement
     */
    public $last_child = null;

    public $winged_unic_id = null;

    public $childs = null;

    function __construct($args = [])
    {
        $this->document = $this;
        foreach ($args as $key => $value) {
            $this->{$key} = $value;
        }
    }

    function __set($name, $value)
    {
        $this->{$name} = $value;
    }


}