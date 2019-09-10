<?php

namespace Winged\Utils;

use Winged\App\App;
use Winged\Buffer\Buffer;

class Log
{

    static $currentNicer = [];

    private static function channeling($nicers = [])
    {
        Buffer::reset();
        ?>
        <!DOCTYPE html>
        <html lang="<?= \WingedConfig::$config->HTML_LANG ?>">
        <head>
            <title>Debug</title>
            <style>
                body {
                    background: #313131;
                    color: #e4e4e4;
                    padding-bottom: 15px;
                }

                * {
                    margin: 0;
                }

                li.hable {
                    padding-left: 15px;
                    cursor: pointer;
                    transition: 0.3s ease all;
                }

                li.hable:before {
                    content: ' ';
                    width: 9px;
                    height: 9px;
                    display: block;
                    position: absolute;
                    left: 0;
                    top: 6px;
                    background: #e4e4e4;
                    clip-path: polygon(100% 50%, 0 0, 0 100%);
                    transition: 0.15s ease all;
                }

                li.hable.habled:before {
                    transform: rotate(90deg);
                }

                ul li ul li ul {
                    display: none;
                }

                li button{
                    background: 0;
                    border: 2px solid #8352ff;
                    border-radius: 4px;
                    color: #8352ff;
                    margin: 0 4px;
                    cursor: pointer;
                    transition: 0.3s ease all;
                    font-weight: 700;
                    font-family: monospace;
                    outline: none;
                }

                li button.expand-all{
                    border: 2px solid #13c152;
                    color: #13c152;
                }

                li button.expand-all:hover{
                    background: #13c152;
                    color: #313131;
                }

                li button.close-all{
                    border: 2px solid #ff5fa3;
                    color: #ff5fa3;
                }

                li button.close-all:hover{
                    background: #ff5fa3;
                    color: #313131;
                }

                ul.expanded {
                    display: block;
                }

                ul.force-none {
                    display: none;
                }

                ul {
                    list-style: none;
                    padding-left: 30px;
                    position: relative;
                }

                ul:before {
                    content: ' ';
                    position: absolute;
                    height: 100%;
                    border-left: 1px dashed #e4e4e4;
                    left: 2px;
                }

                ul.no-padding {
                    padding: 15px 15px 0;
                    font-family: monospace;
                    font-size: 14px;
                }

                ul.no-padding:before {
                    display: none;
                }

                ul li {
                    margin-bottom: 5px;
                    position: relative;
                }

                .object {
                    color: #984ef6;
                    font-weight: 700;
                }

                .array {
                    color: #f6cc55;
                    font-weight: 700;
                }

                .protected {
                    color: #f68222;
                    font-weight: 700;
                }

                .private {
                    color: #f62d00;
                    font-weight: 700;
                }

            </style>
        </head>
        <body>
        <?php
        if (is_array($nicers)) {
            if (!empty($nicers)) {
                foreach ($nicers as $nicer) {
                    echo $nicer;
                }
            }
        }
        ?>
        <script>
            function isHidden(el) {
                let style = window.getComputedStyle(el);
                return (style.display === 'none')
            }

            let lis = document.querySelectorAll('li + .expand');
            for (let li in lis) {
                li = lis[li];
                if (li instanceof HTMLLIElement) {
                    let ul = li.querySelectorAll('ul');
                    if (ul.length > 0) {
                        ul = ul[0];
                        ul.parentNode.previousSibling.classList.add('hable');

                        let closeAll = document.createElement('button');
                        closeAll.classList.add('close-all');
                        closeAll.innerHTML = 'minus all';

                        let expandAll = document.createElement('button');
                        expandAll.classList.add('expand-all');
                        expandAll.innerHTML = 'expand all';

                        ul.parentNode.previousSibling.appendChild(expandAll);
                        ul.parentNode.previousSibling.appendChild(closeAll);

                        ul.parentNode.previousSibling.querySelectorAll('.expand-all')[0].addEventListener('click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();
                            let memo = this.parentNode.nextSibling.querySelectorAll('li.expand');
                            this.parentNode.nextSibling.classList.add('expanded');
                            this.parentNode.nextSibling.classList.remove('force-none');
                            this.parentNode.nextSibling.childNodes[0].classList.add('expanded');
                            this.parentNode.nextSibling.childNodes[0].classList.remove('force-none');
                            this.parentNode.classList.add('habled');
                            for (let i in memo) {
                                let cli = memo[i];
                                if (cli instanceof HTMLLIElement) {
                                    cli.classList.add('expanded');
                                    cli.classList.remove('force-none');
                                    cli.childNodes[0].classList.add('expanded');
                                    cli.childNodes[0].classList.remove('force-none');
                                    cli.previousSibling.classList.add('habled');
                                }
                            }
                        }, false);

                        ul.parentNode.previousSibling.querySelectorAll('.close-all')[0].addEventListener('click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();
                            let memo = this.parentNode.nextSibling.querySelectorAll('li.expand');
                            this.parentNode.nextSibling.classList.remove('expanded');
                            this.parentNode.nextSibling.classList.add('force-none');
                            this.parentNode.nextSibling.childNodes[0].classList.remove('expanded');
                            this.parentNode.nextSibling.childNodes[0].classList.add('force-none');
                            this.parentNode.classList.remove('habled');
                            for (let i in memo) {
                                let cli = memo[i];
                                if (cli instanceof HTMLLIElement) {
                                    cli.classList.remove('expanded');
                                    cli.classList.add('force-none');
                                    cli.childNodes[0].classList.remove('expanded');
                                    cli.childNodes[0].classList.add('force-none');
                                    cli.previousSibling.classList.remove('habled');
                                }
                            }
                        }, false);

                        ul.parentNode.previousSibling.addEventListener('click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();
                            if (this.nextSibling.classList.contains('expanded')) {
                                this.nextSibling.classList.remove('expanded');
                                this.nextSibling.classList.add('force-none');
                                this.nextSibling.childNodes[0].classList.remove('expanded');
                                this.nextSibling.childNodes[0].classList.add('force-none');
                                this.classList.remove('habled');
                            } else {
                                this.nextSibling.classList.add('expanded');
                                this.nextSibling.classList.remove('force-none');
                                this.nextSibling.childNodes[0].classList.add('expanded');
                                this.nextSibling.childNodes[0].classList.remove('force-none');
                                this.previousSibling.classList.add('habled');
                            }
                        }, false);
                        if (!isHidden(ul)) {
                            ul.classList.add('expanded');
                            ul.parentNode.classList.add('expanded');
                            ul.parentNode.previousSibling.classList.add('habled');
                        }
                    }
                }
            }
        </script>
        </body>
        </html>
        <?php
        App::getResponse()->dispatchHtml(Buffer::getKill());
    }

    public static function debug($log = false)
    {
        $title = 'Raw value';
        if (is_array($log) && empty($log)) {
            $title = 'Empty Array: ';
        } else if (is_null($log)) {
            $title = 'Null argument: ';
        } else if (is_bool($log) && $log === true) {
            $title = 'True value argument: ';
        } else if (is_bool($log) && $log === false) {
            $title = 'False value argument: ';
        } else if (is_int($log)) {
            $title = 'Integer value: ';
        } else if (is_string($log)) {
            $title = 'String value: ';
        } else if (is_float($log)) {
            $title = 'Float value: ';
        } else if (is_double($log)) {
            $title = 'Double value: ';
        } else if (is_resource($log)) {
            $title = 'Resource value: ';
        } else if (is_object($log)) {
            $title = 'Object value: ';
        }

        $printed = print_r($log, true);
        $printed = explode("\n", $printed);

        function getTabs($line)
        {
            $offset = 0;
            while (is_int(stripos($line, '    ', $offset))) {
                $offset++;
            }
            return $offset;
        }

        $prevLine = null;
        $currentTabCount = 0;
        $parsed = [];

        $keyCount = 0;

        foreach ($printed as $key => $line) {
            $trimLine = trim($line);
            if ($trimLine === '') {
                continue;
            }

            if ($trimLine === '*RECURSION*') {
                if (isset($parsed[$keyCount - 1])) {
                    $parsed[$keyCount - 1]['line'] .= '<span class="recursion"> { Recursion }</span>';
                    continue;
                }
            }

            $parsed[$keyCount] = [
                'line' => $trimLine,
                'type' => 'li',
                'tabCount' => 0,
                'openUl' => false,
                'closeUl' => false
            ];

            $tabCount = getTabs($line);
            $parsed[$keyCount]['tabCount'] = $tabCount;
            if ($tabCount < $currentTabCount) {
                $currentTabCount = $tabCount;
            }

            if ($tabCount > 0) {
                if ($trimLine === '(') {
                    $parsed[$keyCount]['openUl'] = true;
                }
                if ($trimLine === ')') {
                    $parsed[$keyCount]['closeUl'] = true;
                }
            }

            if ($trimLine === '(') {
                if (isset($printed[$keyCount + 1])) {
                    if (getTabs($printed[$keyCount + 1]) > 0 && getTabs($printed[$keyCount + 1]) > $tabCount) {
                        $parsed[$keyCount]['openUl'] = true;
                    }
                }
            }

            if ($trimLine === ')') {
                if (isset($parsed[$keyCount - 1])) {
                    if ($parsed[$keyCount - 1]['tabCount'] > 0 && $parsed[$keyCount - 1]['tabCount'] > $tabCount) {
                        $parsed[$keyCount]['closeUl'] = true;
                    }
                }
            }
            $keyCount++;
        }

        $html = '<ul class="no-padding"><li class="title">' . $title . '</li>';
        foreach ($parsed as $key => $parse) {
            if ($parse['openUl']) {
                $html .= '<li class="expand"><ul>';
                continue;
            }
            if ($parse['closeUl']) {
                $html .= '</ul></li>';
                continue;
            }
            $html .= '<li>' . $parse['line'] . '</li>';
        }

        $html .= '</ul>';
        $html = str_replace('</li><li class="expand"><ul></ul></li>', '<span class="empty"> { Empty }</span></li>', $html);
        $html = str_replace('Array', '<span class="array">Array</span>', $html);
        $html = str_replace('Object', '<span class="object">Object</span>', $html);
        $html = str_replace('Float', '<span class="float">Float</span>', $html);
        $html = str_replace('Double', '<span class="double">Double</span>', $html);
        $html = str_replace('Integer', '<span class="integer">Integer</span>', $html);
        $html = str_replace('String', '<span class="string">String</span>', $html);
        $html = str_replace('Resource', '<span class="resource">Resource</span>', $html);
        $html = str_replace('protected', '<span class="protected">protected</span>', $html);
        $html = str_replace('private', '<span class="private">private</span>', $html);
        self::channeling([$html]);
    }
}