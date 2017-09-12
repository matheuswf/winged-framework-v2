<?php

class FileTree {

    private $folders = [];

    private function getTree($path, $ext = false) {
        $files = array();
        if ($path[strlen($path) - 1] != "/") {
            $path .= "/";
        }
        if(!file_exists($path)){
            return false;
        }
        $sh = scandir($path);
        $vect = false;
        $bpath = false;
        for ($x = 2; $x < count($sh); $x++) {
            if($sh[$x] != '.' && $sh[$x] != '..'){
                $npath = $path . $sh[$x];
                if (is_directory($npath)) {
                    $this->folders[] = $npath;
                    $vect = $this->getTree($npath);
                    $bpath = $npath;
                    if ($vect) {
                        for ($y = 0; $y < count($vect); $y++) {
                            $file = $sh[$x] . "/" . $vect[$y];
                            if($ext){
                                $exp = explode('.', $file);
                                $end = array_pop($exp);
                                if(is_array($ext)){
                                    if(in_array($end, $ext)){
                                        $files[] = $file;
                                    }
                                }else if(is_string($ext)){
                                    if($end == $ext){
                                        $files[] = $file;
                                    }
                                }
                            }else{
                                $files[] = $file;
                            }
                        }
                        $vect = false;
                    }
                } else {
                    if($ext){
                        $exp = explode('.', $sh[$x]);
                        $end = array_pop($exp);
                        if(is_array($ext)){
                            if(in_array($end, $ext)){
                                $files[] = $sh[$x];
                            }
                        }
                        else if(is_string($ext)){
                            if($end == $ext){
                                $files[] = $sh[$x];
                            }
                        }
                    }else{
                        $files[] = $sh[$x];
                    }
                }
            }
        }
        return $files;
    }

    public function gemTree($path, $ext = false) {
        $this->folders = [];
        $all = $this->getTree($path, $ext);
        if(!$all){
            return false;
        }
        if ($path[strlen($path) - 1] != "/") {
            $path .= "/";
        }
        if(empty($all) && !empty($this->folders)){
            $all = $this->folders;
            $this->folders = [];
            return $all;
        }
        for ($x = 0; $x < count($all); $x++) {
            $name = $path . $all[$x];
            $all[$x] = array();
            $all[$x]["path"] = $name;
            $all[$x]["size"] = filesize($name);
            $arr = explode("/", $name);
            array_pop($arr);
            $all[$x]["folder"] = join("/", $arr) . "/";
            $all[$x]["modified"] = date("Y-m-d H:i:s", filemtime($name));
            $all[$x]["created"] = date("Y-m-d H:i:s", filectime($name));
            $all[$x]["ts_modified"] = strtotime($all[$x]["modified"]);
            $all[$x]["ts_created"] = strtotime($all[$x]["created"]);
        }
        return $all;
    }

    public function gemFolderList($tree){
        if(!$tree){
            return false;
        }
        if(array_key_exists(0, $tree)){
            if(is_array($tree[0]) && array_key_exists('path', $tree[0])){
                $folders = array();
                foreach ($tree as $key => $value) {
                    $file = $value["path"];
                    $exp = explode("/", $file);
                    array_pop($exp);
                    $folder = "";
                    for($x = 0; $x < count($exp); $x++){
                        if($x == 0){
                            $folder = $exp[$x] . "/";
                        }else{
                            $folder .= $exp[$x] . "/";
                        }

                        if(!in_array($folder, $folders)){
                            $folders[] = $folder;
                        }
                    }
                }

                $counts = array();

                for($x = 0; $x < count($folders); $x++){
                    $exp = explode("/", $folders[$x]);
                    if(!array_key_exists(count($exp), $counts)){
                        $counts[count($exp)] = array();
                    }
                    array_push($counts[count($exp)], $folders[$x]);
                }

                $counts = array_reverse($counts, false);

                $folders = array();

                for($x = 0; $x < count($counts); $x++){
                    for($y = 0; $y < count($counts[$x]); $y++){
                        array_push($folders, $counts[$x][$y]);
                    }
                }
                return $folders;
            }else{
                $order = [];
                foreach($tree as $folder){
                    $exp = explode('/', $folder);
                    if(!array_key_exists(count($exp), $order)){
                        $order[count($exp)] = [];
                    }
                    $order[count($exp)][] = $folder;
                }
                ksort($order);
                $folders = [];
                $order = array_reverse($order);
                foreach($order as $ord){
                    foreach($ord as $folder){
                        $folders[] = $folder;
                    }
                }
                return $folders;
            }
        }
        return false;
    }

    public function gemFileList($path, $savein = false, $mod = false, $filename = "file.list.json") {
        if (!$savein) {
            $savein = $path;
        }
        if ($filename != "file.list.json") {
            $filename .= ".json";
        }
        $seconds = 0;
        $json = "";
        $all = $this->gemTree($path);
        $now = strtotime(date("Y-m-d H:i:s"));
        $list = array();
        if ($mod) {
            foreach ($mod as $key => $value) {
                if ($key == "d") {
                    $seconds += $value * 24 * 60 * 60;
                } else if ($key == "h") {
                    $seconds += $value * 60 * 60;
                } else if ($key == "i") {
                    $seconds += $value * 60;
                } else {
                    $seconds += $value;
                }
            }
            for ($x = 0; $x < count($all); $x++) {
                if ($now - $all[$x]["ts_modified"] < $seconds) {
                    $list[] = $all[$x];
                }
            }
            $json = json_encode($list);
        } else {
            $json = json_encode($all);
        }
        if (file_exists($savein) && is_directory($savein)) {
            $handle = fopen($savein . $filename, "w+");
            fwrite($handle, $json);
            fclose($handle);
            return true;
        }
        return false;
    }
}