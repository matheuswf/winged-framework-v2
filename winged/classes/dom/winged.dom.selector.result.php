<?php

class WingedDOMSelectorResult extends WingedDOMHelper
{
    public $lenght = 0;
    /**
     * @var $list WingedDOMElement[]
     */
    public $list = [];

    public $main_ref = null;
    public $fullSelector = '';


    public function __construct($searchFilter = [], WingedDOM $mainDom = null)
    {
        $this->list = $searchFilter;
        $this->lenght = count($searchFilter);
        $this->fullSelector = $mainDom->fullSelector;
        $this->main_ref = $mainDom;
    }

    public function dispatch($function = null, $each = false)
    {
        if (is_callable($function)) {
            if ($each) {
                foreach ($this->list as $key => $child) {
                    $t = call_user_func_array($function, [$child, $key, $this]);
                    if ($t !== null) {
                        return $t;
                    }
                }
            } else {
                $t = call_user_func_array($function, [$this, $this]);
                if ($t !== null) {
                    return $t;
                }
            }
        }
        return $this;
    }

    public function each($function)
    {
        return $this->dispatch($function, true);
    }

    public function find($selector)
    {
        $this->fullSelector .= ' ' . $selector;
        $relist = [];
        if ($this->list != null) {
            foreach ($this->list as $child) {
                $nlist = $child->find($selector)->dispatch();
                if (count($nlist->list) > 0) {
                    foreach ($nlist->list as $nchild) {
                        if (!array_key_exists($nchild->winged_unic_id, $relist)) {
                            $relist[$nchild->winged_unic_id] = $nchild;
                        }
                    }
                }
            }
        }
        $relist = array_values($relist);
        $this->list = $relist;
        $this->lenght = count($relist);
        return $this;
    }

    public function remove(){

    }

    public function text($text = null){
        $texts = [];
        if ($this->list != null) {
            foreach ($this->list as $child) {
                $ret = $child->text($text);
                if($text == null){
                    $texts[] = $ret;
                }
            }
        }
        if(count($texts) == 1){
            return $texts[0];
        }else if(count($texts) > 1){
            return $texts;
        }else{
            return $this;
        }
    }

}