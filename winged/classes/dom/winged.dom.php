<?php

class WDOM
{
    public static $refers = [];
}

class WingedDOM extends WingedDOMHelper
{
    /**
     * @var $document WingedDOMElement
     */
    public $document = null;
    public $initial_document_array = [];
    public $main_dom = null; //xml
    public $main_document = null; //obj
    public $last_selector = null;
    public $unic_refer = null;

    public $searchForwardVar = null;
    public $searchBackwardVar = null;
    public $searchFilter = null;
    public $currentFind = null;
    public $global_refers = null;
    public $fullSelector = null;

    public function __construct($html_xml = '<html></html>')
    {
        $this->unic_refer = uniqid();
        $this->load($html_xml);
        WDOM::$refers[$this->unic_refer] = [
            'obj' => &$this,
            'change' => false,
        ];
    }

    public function getInitalDomArray()
    {
        return $this->initial_document_array;
    }

    public function _clone_point()
    {
        return new WingedDOM($this->build($this->document));
    }

    public function _clone()
    {
        return new WingedDOM($this->build());
    }

    public function build($dom = null, $html = '')
    {

        $cant_close = ['br'];

        if ($dom == null) {
            $dom = WDOM::$refers[$this->unic_refer]['obj']->document;
        }

        $parse = [];

        if (!empty($dom->class)) {
            $parse['class'] = $dom->class;
        }

        if (!empty($dom->id)) {
            $parse['id'] = $dom->class;
        }

        if (!empty($dom->attributes)) {
            $parse = array_merge($parse, $dom->attributes);
        }

        $keys = array_keys($parse);
        foreach ($parse as $key => $value) {
            $to = ' ' . $key;
            $first = false;
            if (is_array($value)) {
                foreach ($value as $number => $ov) {
                    if (!$first) {
                        $first = $ov;
                    } else {
                        $first .= $ov;
                    }
                }
            } else {
                $first = $value;
            }
            if ($key == end($keys)) {
                $to .= '="' . $first . '"';
            } else {
                $to .= '="' . $first . '" ';
            }
            $parse[$key] = $to;
        }

        $html .= '<' . $dom->tagName;
        foreach ($parse as $value) {
            $html .= $value;
        }
        $html .= '>';
        if (count($dom->childs) > 0) {
            foreach ($dom->childs as $child) {
                $html = $this->build($child, $html);
            }
        }
        if ($dom->text != null) {
            $html .= $dom->text;
        }
        if (!in_array($dom->tagName, $cant_close)) {
            $html .= '</' . $dom->tagName . '>';
        }
        return $html;
    }

    public function buildString($dom = null, $html = '', $tab = 0)
    {

        $cant_close = ['br'];

        if ($dom == null) {
            $dom = WDOM::$refers[$this->unic_refer]['obj']->document;
        }
        $iden = "";
        for ($x = 0; $x < $tab; $x++) {
            $iden .= "\t";
        }

        $parse = [];

        if (!empty($dom->class)) {
            $parse['class'] = $dom->class;
        }

        if (!empty($dom->id)) {
            $parse['id'] = $dom->class;
        }

        if (!empty($dom->attributes)) {
            $parse = array_merge($parse, $dom->attributes);
        }

        $keys = array_keys($parse);
        foreach ($parse as $key => $value) {
            $to = " " . $key;
            $first = false;
            if (is_array($value)) {
                foreach ($value as $number => $ov) {
                    if (!$first) {
                        $first = $ov;
                    } else {
                        $first .= $ov;
                    }
                }
            } else {
                $first = $value;
            }
            if ($key == end($keys)) {
                $to .= "=\"" . $first . "\"";
            } else {
                $to .= "=\"" . $first . "\" ";
            }
            $parse[$key] = $to;
        }

        $html .= $iden . "<" . $dom->tagName;
        foreach ($parse as $value) {
            $html .= $value;
        }
        $html .= ">\n";
        if (count($dom->childs) > 0) {
            foreach ($dom->childs as $child) {
                $tab++;
                $html = $this->buildString($child, $html, $tab);
            }
        }
        if ($dom->text != null) {
            $html .= $iden . "\t" . $dom->text . "\n";
        }
        if (!in_array($dom->tagName, $cant_close)) {
            $html .= $iden . "</" . $dom->tagName . ">\n";
        }
        return $html;
    }

    public function remove()
    {
        if ($this->searchFilter != null) {
            foreach ($this->searchFilter as $key => $child) {
                if ($child->parent != null) {
                    foreach ($child->parent->childs as $nkey => $nchild) {
                        if ($nchild->winged_unic_id == $child->winged_unic_id) {
                            $this->rmElement($child->winged_unic_id);
                        }
                    }
                } else {
                    unset($this->global_refers[$this->document->winged_unic_id]);
                    $this->document = null;
                    return $this;
                }
            }
        } else {
            if ($this->document->parent != null) {
                $this->rmElement($this->document->winged_unic_id);
            } else {
                unset(WDOM::$refers[$this->unic_refer]);
                $this->document = null;
                $this->initial_document_array = null;
                $this->main_dom = null;
                $this->main_document = null;
                $this->last_selector = null;
                $this->unic_refer = null;
                $this->searchForwardVar = null;
                $this->searchBackwardVar = null;
                $this->searchFilter = null;
                $this->currentFind = null;
                $this->global_refers = null;
                $this->fullSelector = null;
                return false;
            }
        }
        $this->searchFilter = null;
        $this->remake();
        return $this;
    }

    public function text($text = null)
    {
        $texts = [];
        if ($this->searchFilter != null) {
            foreach ($this->searchFilter as $key => $child) {
                $text_el = $this->searchFilter[$key]->text == null ? '' : $this->searchFilter[$key]->text;
                if ($text != null) {
                    $this->searchFilter[$key]->text = $text;
                } else {
                    if (count($this->searchFilter) == 1) {
                        $texts = $text_el;
                    } else {
                        $texts[] = $text_el;
                    }
                }
            }
        } else {
            $text_el = $this->document->text == null ? '' : $this->document->text;
            if ($text != null) {
                $this->document->text = $text;
            } else {
                $texts = $text_el;
            }
        }
        $this->remake();
        $this->useRefactor();
        $this->searchFilter = null;
        if (!empty($texts) || is_string($texts)) {
            return $texts;
        }
        return $this;
    }

    private function rmElement($element_id, &$dom = null, WingedDOMElement &$father = null, &$key = null)
    {
        WDOM::$refers[$this->unic_refer]['change'] = true;
        $refer = $dom;
        if ($refer === null) {
            $refer = &WDOM::$refers[$this->unic_refer]['obj']->main_document;
        }
        if ($refer->winged_unic_id == $element_id) {
            if ($father == null) {
                unset($dom);
                return false;
            } else {
                unset($father->childs[$key]);
                $father->childs = array_values($father->childs);
            }
        } else {
            if ($refer->childs != null) {
                foreach ($refer->childs as $key => &$child) {
                    $this->rmElement($element_id, $refer->childs[$key], $refer, $key);
                }
            }
        }
    }

    private function useRefactor()
    {
        if ($this->searchFilter != null || count($this->searchFilter) > 0) {
            $this->refactor($this->searchFilter);
        } else {
            $list = [$this->document];
            $this->refactor($list);
            if (count($list) > 0) {
                $this->document = $list[0];
            }
        }
    }

    private function refactor(&$list)
    {
        if ($list != null && count($list) > 0) {
            foreach ($list as $key => $child) {
                $found = false;
                $this->realRefactor($list, $key, $found);
                if (!$found) {
                    unset($list[$key]);
                }
            }
        }
        array_values($list);
    }

    private function realRefactor(&$list = null, &$mkey = null, &$found = false, &$dom_ref = null)
    {
        $refer = $dom_ref;
        if ($refer === null) {
            $refer = &WDOM::$refers[$this->unic_refer]['obj']->main_document;
        }

        if ($refer->winged_unic_id == $list[$mkey]->winged_unic_id) {
            $list[$mkey] = $refer;
            $list[$mkey]->document = $refer;
            $found = true;
        }

        if (count($refer->childs) > 0) {
            foreach ($refer->childs as $key => $child) {
                $this->realRefactor($list, $mkey, $found, $child);
            }
        }
    }

    private function remake(&$dom = null)
    {
        $last = null;
        $current = null;
        $refer = $dom;
        if ($refer === null) {
            $refer = &WDOM::$refers[$this->unic_refer]['obj']->main_document;
        }
        if (count($refer->childs) >= 1) {
            foreach ($refer->childs as $number => $child) {
                $refer->childs[$number]->parent = &$refer;
                if ($number === 0) {
                    $last = &$refer->childs[$number];
                    $current = &$refer->childs[$number];
                } else if ($number > 0 && $number != count($refer->childs) - 1) {
                    $current->next_brother = $child;
                    $refer->childs[$number]->prev_brother = $last;
                    $last = &$child;
                    $current = &$refer->childs[$number];
                } else if ($number == count($refer->childs) - 1) {
                    $current->next_brother = $child;
                    $refer->childs[$number]->prev_brother = $last;
                }
            }
            $refer->last_child = end($refer->childs);
            $refer->first_child = $refer->childs[0];
            foreach ($refer->childs as $number => $child) {
                $this->remake($refer->childs[$number]);
            }
        } else {
            $refer->last_child = null;
            $refer->first_child = null;
        }
        $refer->document = $refer;
    }

    public function dispatch($function = null, $each = false)
    {
        $this->remake();
        $this->useRefactor();
        $ret = new WingedDOMSelectorResult($this->searchFilter, $this);
        $this->searchFilter = null;
        $this->fullSelector = '';
        if (is_callable($function)) {
            if ($each) {
                foreach ($ret->list as $key => $child) {
                    $t = call_user_func_array($function, [$child, $key, $ret]);
                    if ($t !== null) {
                        return $t;
                    }
                }
            } else {
                $t = call_user_func_array($function, [$ret, $ret]);
                if ($t !== null) {
                    return $t;
                }
            }
        }
        return $ret;
    }

    public function each($function)
    {
        return $this->dispatch($function, true);
    }

    public function nthChild($factor = '')
    {

        $this->fullSelector .= ' nth-child(' . $factor . ')';

        $find = $this->selector($this->last_selector);
        $find = end($find);

        if ($this->searchFilter != null) {
            $factor = str_replace(' ', '', $factor);
            if ($factor == 'odd') {
                $factor = '2n+1';
            } else if ($factor == 'even') {
                $factor = '2n';
            }
            $exp = [0, 0];
            if (is_int(stripos($factor, 'n'))) {
                if (is_int(stripos($factor, '+'))) {
                    $exp = explode('n+', $factor);
                } else {
                    $exp = explode('n', $factor);
                }
            } else {
                if (is_int(stripos($factor, '+'))) {
                    $exp = explode('+', $factor);
                }
            }

            $factor = 0;
            if (isset($exp[0])) {
                $factor = $exp[0];
            }
            $jump = 0;
            if (isset($exp[1])) {
                $jump = $exp[1];
            }

            $realy_zero = true;

            if ($jump > 0) {
                $jump--;
                $realy_zero = false;
            }

            $factor_sum = $factor;

            $filter = [];

            if (!empty($find) && $find != null) {
                foreach ($this->searchFilter as $key => $value) {
                    $childs = [];
                    $factor = $factor_sum;
                    if ($realy_zero) {
                        $factor--;
                    }
                    if ($value->parent != null) {
                        foreach ($value->parent->childs as $child) {
                            if ($this->isSelected($child, $find)) {
                                $childs[] = $child;
                            }
                        }

                        if (array_key_exists($jump, $childs)) {
                            if (!array_key_exists($childs[$jump]->winged_unic_id, $filter)) {
                                $filter[$childs[$jump]->winged_unic_id] = $this->searchFilter[$jump];
                            }
                        }

                        if ($realy_zero) {
                            $factor = ($jump + $factor);
                        } else {
                            $factor = ($jump + $factor_sum);
                        }
                        for ($x = $jump; $x < count($childs); $x++) {
                            if ($x == $factor) {
                                if (array_key_exists($x, $childs)) {
                                    if (!array_key_exists($childs[$x]->winged_unic_id, $filter)) {
                                        $filter[$childs[$x]->winged_unic_id] = $this->searchFilter[$x];
                                    }
                                }
                                $factor += $factor_sum;
                            }
                        }
                    }
                }
            }
            $filter = array_values($filter);
            $this->searchFilter = $filter;
        }
        return $this;

    }

    public function fisrtChild()
    {
        if ($this->searchFilter != null) {
            if(count($this->searchFilter) >= 1){
                $pop = array_shift($this->searchFilter);
                $this->searchFilter = [];
                $this->searchFilter[] = $pop;
            }
        }
        return $this;
    }

    public function lastChild()
    {
        if ($this->searchFilter != null) {
            if(count($this->searchFilter) >= 1){
                $pop = array_pop($this->searchFilter);
                $this->searchFilter = [];
                $this->searchFilter[] = $pop;
            }
        }
        return $this;
    }

    public function notFirstChild()
    {
        if ($this->searchFilter != null) {
            foreach ($this->searchFilter as $key => $value) {
                if ($value->prev_brother === null) {
                    unset($this->searchFilter[$key]);
                }
            }
            array_values($this->searchFilter);
        }
        return $this;
    }

    public function notLastChild()
    {
        if ($this->searchFilter != null) {
            foreach ($this->searchFilter as $key => $value) {
                if ($value->next_brother === null) {
                    unset($this->searchFilter[$key]);
                }
            }
            array_values($this->searchFilter);
        }
        return $this;
    }

    public function not($selector)
    {

        $this->useRefactor();
        $this->fullSelector .= ':not(' . $selector . ')';
        $tofind = $this->selector($selector);
        if (count($tofind) == 1 && $this->searchFilter != null) {
            foreach ($this->searchFilter as $key => $child) {
                if ($this->isSelected($child, $tofind[0])) {
                    unset($this->searchFilter[$key]);
                }
            }
        }
        array_values($this->searchFilter);
        return $this;
    }

    private function pFind($selector, $directFind = false)
    {
        $group = null;
        $tofind = $this->selector($selector);
        $this->currentFind = $this->document;

        if ($directFind) {
            $this->currentFind = $directFind;
        }

        if ($this->searchFilter != null) {
            $filter = $this->searchFilter;
            $this->searchFilter = null;
            foreach ($filter as $key => $value) {
                if (empty($group) || count($group) == 0 || $group == null) {
                    $group = [];
                }
                $ret = array_values($this->pFind($selector, $value));
                foreach ($ret as &$child) {
                    if (!array_key_exists($child->winged_unic_id, $group)) {
                        $group[$child->winged_unic_id] = &$child;
                    }
                }
            }
            if (empty($group) || count($group) == 0 || $group == null) {
                $group = [];
            }
            $group = array_values($group);
            $this->searchFilter = $group;
            $this->currentFind = null;
            return $this;
        } else {
            foreach ($tofind as $key => $find) {
                if ($key == 0) {
                    if (array_key_exists('level', $find)) {
                        $this->searchForwardOneLevel($this->currentFind, $find);
                    } else {
                        $this->searchForward($this->currentFind, $find, $this->currentFind);
                    }
                    $group = $this->searchForwardVar;
                    $this->searchForwardVar = [];
                    if (empty($group) || count($group) == 0) {
                        return $this;
                    }
                } else {
                    if ($group != null) {
                        $newgroup = [];
                        foreach ($group as $okey => $child) {
                            if (array_key_exists('level', $find)) {
                                $this->searchForwardOneLevel($child, $find);
                            } else {
                                $this->searchForward($child, $find, $child);
                            }
                            $regroup = $this->searchForwardVar;
                            $this->searchForwardVar = [];
                            foreach ($regroup as &$nchild) {
                                if (!array_key_exists($nchild->winged_unic_id, $newgroup)) {
                                    $newgroup[$nchild->winged_unic_id] = &$nchild;
                                }
                            }
                        }
                        $group = array_values($newgroup);
                    }
                }
            }
            if ($this->searchFilter != null || $directFind) {
                return array_values($group);
            }
            $this->searchFilter = array_values($group);
            return $this;
        }
    }

    private function pClosest($selector)
    {
        $group = null;
        $tofind = $this->selector($selector);
        if ($this->searchFilter != null) {
            foreach ($tofind as $key => $find) {
                if ($key == 0) {
                    foreach ($this->searchFilter as $child) {
                        $this->searchBackward($child, $find);
                    }
                    $group = array_values($this->searchBackwardVar);
                    $this->searchBackwardVar = [];
                    if (empty($group) || count($group) == 0) {
                        return $this;
                    }
                } else {
                    if ($group != null) {
                        foreach ($group as $okey => $child) {
                            $this->searchBackward($child, $find);
                            $regroup = array_values($this->searchBackwardVar);
                            $this->searchBackwardVar = [];
                            if (empty($regroup) || count($regroup) == 0) {
                                unset($group[$okey]);
                            } else {
                                unset($group[$okey]);
                                $group[$okey] = $regroup;
                            }
                        }
                        foreach ($group as $okey => $child) {
                            foreach ($child as $ykey => $value) {
                                $group[] = $value;
                            }
                            unset($group[$okey]);
                        }
                    }
                }
            }
        }
        if ($group == null) {
            $group = [];
        }
        $this->searchFilter = array_values($group);
        return $this;
    }

    /**
     * @param $selector
     * @return WingedDOM
     */
    public function closest($selector)
    {
        $this->last_selector = $selector;
        return $this->pClosest($selector);
    }

    /**
     * @param $selector
     * @return WingedDOM
     */
    public function find($selector)
    {
        $this->useRefactor();
        $this->fullSelector .= ' ' . $selector;
        $this->last_selector = $selector;
        return $this->pFind($selector);
    }

    private function searchForward($dom, $find, $odom)
    {
        $register = false;

        if ($odom->winged_unic_id != $dom->winged_unic_id) {
            $register = $this->isSelected($dom, $find);
        }

        if (count($dom->childs) > 0) {
            foreach ($dom->childs as &$child) {
                $this->searchForward($child, $find, $odom);
            }
        }

        if ($register) {
            $this->searchForwardVar[] = $dom;
        }
    }

    private function searchForwardOneLevel($dom, $find)
    {
        if (count($dom->childs) > 0) {
            foreach ($dom->childs as &$child) {
                if ($this->isSelected($child, $find)) {
                    $this->searchForwardVar[] = $child;
                }
            }
        }
    }

    private function searchBackward($dom, $find)
    {
        if ($dom->parent != null) {
            $register = $this->isSelected($dom->parent, $find);
            if ($register) {
                if (!array_key_exists($dom->parent->winged_unic_id, $this->searchBackwardVar)) {
                    $this->searchBackwardVar[$dom->parent->winged_unic_id] = $dom->parent;
                }
            }
            $this->searchBackward($dom->parent, $find);
        }
    }


    /**
     * @param $html_xml string
     * @return bool | WingedDOM
     */
    function load($html_xml = '<html></html>')
    {
        if (file_exists($html_xml) && !is_dir($html_xml) && is_readable($html_xml)) {
            $content = file_get_contents($html_xml);
            $loaded = $this->abLoad($content);
        } else {
            $loaded = $this->abLoad($html_xml);
        }

        if ($loaded) {
            $keys = array_keys($this->initial_document_array);
            $exp = explode('_;_', $keys[0]);
            $key = array_shift($exp);
            $this->document = null;
            $this->initial_document_array = [$key => $this->normalizeDomArray($this->initial_document_array)];
            $this->document = $this->parseDom($this->initial_document_array);
            $this->document = $this->normalizeDom($this->document);
            $this->main_document = &$this->document;
        }

        return $this;
    }

    /**
     * @param $html_xml string
     * @return bool | WingedDOM
     */
    private function abLoad($html_xml = '<html></html>')
    {
        try {
            $xml = new DOMDocument();
            $xml->loadXML($html_xml);
            $this->main_dom = $xml;
            $this->initial_document_array = $this->removeTextTag($this->xmlToArray($xml));
            return true;
        } catch (Exception $e) {
            Winged::push_warning(__CLASS__, 'Can\'t load HTML or XML document', true);
            return false;
        }
    }

    /**
     * Example: array [tag name => content array]
     * @param $init_dom array
     * @return WingedDOMElement
     */
    private function parseDom($init_dom)
    {
        $keys = array_keys($init_dom);
        $tag_name = $keys[0];
        $before = null;
        if (!array_key_exists('childs', $init_dom)) {
            $el = $this->makeElement($init_dom[$tag_name], $tag_name);
            if (array_key_exists('childs', $init_dom[$tag_name])) {
                foreach ($init_dom[$tag_name]['childs'] as $number => $tag) {
                    $el->childs[] = $this->parseDom($tag);
                }
            }
            return $el;
        } else {
            return $this->makeElement($init_dom[$tag_name], $tag_name);
        }
    }

    /**
     * @param $dom WingedDOMElement
     * @return WingedDOMElement
     */
    private function normalizeDom($dom)
    {
        $last = null;
        $current = null;
        if ($dom->childs != null) {
            if (count($dom->childs) >= 1) {
                foreach ($dom->childs as $number => $child) {
                    $dom->childs[$number]->parent = &$dom;
                    if ($number === 0) {
                        $last = &$dom->childs[$number];
                        $current = &$dom->childs[$number];
                    } else if ($number > 0 && $number != count($dom->childs) - 1) {
                        $current->next_brother = $child;
                        $dom->childs[$number]->prev_brother = $last;
                        $last = &$child;
                        $current = &$dom->childs[$number];
                    } else if ($number == count($dom->childs) - 1) {
                        $current->next_brother = $child;
                        $dom->childs[$number]->prev_brother = $last;
                    }
                }
                $dom->last_child = end($dom->childs);
                $dom->first_child = $dom->childs[0];
                foreach ($dom->childs as $number => $child) {
                    $dom->childs[$number] = $this->normalizeDom($child);
                }
            } else {
                $dom->last_child = null;
                $dom->first_child = null;
            }
        }
        return $dom;
    }

    /**
     * Example: $dom[$tag_name], $tag_name
     * @param $tag array
     * @param $key string
     * @return WingedDOMElement
     */
    private function makeElement($tag, $key)
    {
        $doc = new WingedDOMElement(
            [
                'tagName' => $key,
                'winged_unic_id' => uniqid(),
                'unic_refer' => $this->unic_refer,
                'main_document' => &$this->main_document,
            ]
        );

        //$this->global_refers[$doc->winged_unic_id] = &$doc;

        //$doc->global_refers = &$this->global_refers;

        if (array_key_exists('@text', $tag)) {
            $doc->text = trim($tag['@text']);
            $doc->origText = $tag['@text'];
            unset($tag['@text']);
        }

        if (array_key_exists('@attributes', $tag)) {
            if (array_key_exists('class', $tag['@attributes'])) {
                $doc->class = [];
                $exp = explode(' ', $tag['@attributes']['class']);
                foreach ($exp as $class) {
                    $doc->class[] = $class;
                }
                unset($tag['@attributes']['class']);
            }
            if (array_key_exists('id', $tag['@attributes'])) {
                $doc->id = $tag['@attributes']['id'];
                unset($tag['@attributes']['id']);
            }

            $doc->attributes = [];

            foreach ($tag['@attributes'] as $att => $attr) {
                $doc->attributes[$att] = $attr;
            }
            unset($tag['@attributes']);
        }
        return $doc;
    }

    /**
     * @param $init_dom array
     * @return bool | array
     */
    private function normalizeDomArray($init_dom)
    {
        $not = ['@attributes', '@text'];
        foreach ($init_dom as $tag_name => $el) {
            $exp = explode('_;_', $tag_name);
            $tg = array_shift($exp);
            $init_dom[$tg] = $init_dom[$tag_name];
            unset($init_dom[$tag_name]);
            $guard = [];
            foreach ($init_dom[$tg] as $prop => $undefined) {
                if (in_array($prop, $not)) {
                    $guard[$prop] = $init_dom[$tg][$prop];
                    unset($init_dom[$tg][$prop]);
                }
            }
            if ($this->checkIfIsNodeArray($init_dom[$tg])) {
                unset($init_dom[$tg]['@node']);
                if ($this->checkHasChildsArray($init_dom[$tg])) {
                    $init_dom[$tg]['childs'] = [];
                    foreach ($init_dom[$tg] as $prop => $undefined) {
                        if ($prop != 'childs') {
                            $init_dom[$tg]['childs'][$prop] = $init_dom[$tg][$prop];
                            unset($init_dom[$tg][$prop]);
                        }
                    }
                    foreach ($guard as $prop => $value) {
                        $init_dom[$tg][$prop] = $value;
                    }

                    foreach ($init_dom[$tg]['childs'] as $prop => $undefined) {
                        $exp = explode('_;_', $prop);
                        $clear = array_shift($exp);
                        $init_dom[$tg]['childs'][] = [$clear => $this->normalizeDomArray([$prop => $init_dom[$tg]['childs'][$prop]])];
                        unset($init_dom[$tg]['childs'][$prop]);
                    }
                    return $init_dom[$tg];
                } else {
                    foreach ($guard as $prop => $value) {
                        $init_dom[$tg][$prop] = $value;
                    }
                    return $init_dom[$tg];
                }
            }
        }
        return false;
    }

    /**
     * Example: $dom[$tag_name]
     * @param $tag
     * @return bool
     */
    private function checkHasChildsArray($tag)
    {
        $not = ['@attributes', '@text', '@node'];
        foreach ($tag as $prop => $undefined) {
            if (in_array($prop, $not)) {
                unset($tag[$prop]);
            }
        }
        if (count($tag) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Example: $dom[$tag_name]
     * @param $tag
     * @return bool
     */
    private function checkIfIsNodeArray($tag)
    {
        if (array_key_exists('@node', $tag)) {
            return true;
        }
        return false;
    }

    /**
     * @param $array array
     * @return mixed
     */
    private function removeTextTag($array)
    {
        foreach ($array as $key => $value) {
            $exp = explode('_;_', $key);
            $exp = array_shift($exp);
            if (is_array($value) && $exp != '#text') {
                $array[$key] = $this->removeTextTag($value);
            }
            if ($exp === '#text') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * @param $root DOMDocument
     * @return array
     */
    private function xmlToArray($root)
    {
        $result = array();

        if ($root->nodeType != XML_DOCUMENT_NODE) {
            $result['@node'] = true;
        }

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['@text'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['text']
                        : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                $key = $child->nodeName . "_;_" . uniqid();
                if (!isset($result[$key])) {
                    $result[$key] = $this->xmlToArray($child);
                } else {
                    if (!isset($groups[$key])) {
                        $result[$key] = array($result[$key]);
                        $groups[$key] = 1;
                    }
                    $result[$key][] = $this->xmlToArray($child);
                }
            }
        }
        return $result;
    }
}