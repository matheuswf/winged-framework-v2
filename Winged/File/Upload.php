<?php

namespace Winged\Upload;

use Winged\Formater\Formater;

/**
 * Class Upload
 *
 * @package Winged\Upload
 */
class Upload
{
    private $allow = [
        "img" => [".jpg", ".jpeg", ".png", ".tiff", ".gif"],
        "doc" => [".docx", ".txt", ".doc", ".pdf", ".csv"],
        "zip" => [".zip", ".rar", ".tar", ".tar.gz"],
        "audio" => [".mp3", ".ogg", ".wav"],
        "video" => [".mp4", ".avi", ".mpeg", ".wmv", ".webm", ".ogg", ".mov"],
    ];
    private $allowed = [], $max_size, $path, $lastuploaded = [], $nametype;

    public function setOptions($path, $type = "", $add = "", $allow_no = "", $max_size = 64, $nametype = "token")
    {
        ini_set('post_max_size', $max_size . 'M');
        ini_set('upload_max_filesize', $max_size . 'M');
        $types = explode(",", $type);
        $adds = explode(",", $add);
        $allown = explode(",", $allow_no);
        $merge = [];
        $right = [];

        if (count7($types) > 1) {
            for ($x = 0; $x < count7($types); $x++) {
                if (array_key_exists(trim($types[$x]), $this->allow)) {
                    if ($x == 0) {
                        $merge = $this->allow[trim($types[$x])];
                    } else {
                        $merge = array_merge($merge, $this->allow[trim($types[$x])]);
                    }
                }
            }
        } else {
            if ($type != "") {
                if (array_key_exists($type, $this->allow)) {
                    $merge = $this->allow[trim($type)];
                }
            }
        }

        for ($x = 0; $x < count7($adds); $x++) {
            if ($adds[$x] != ".") {
                $adds[$x] = "." . trim($adds[$x]);
            } else {
                $adds[$x] = trim($adds[$x]);
            }
            $merge[] = $adds[$x];
        }

        for ($x = 0; $x < count7($allown); $x++) {
            if ($allown[$x] != ".") {
                $allown[$x] = "." . trim($allown[$x]);
            } else {
                $allown[$x] = trim($allown[$x]);
            }
        }

        foreach ($merge as $key => $value) {
            if (in_array($value, $allown)) {
                unset($merge[$key]);
            }
        }

        foreach ($merge as $key => $value) {
            $right[] = $value;
        }
        $this->allowed = $right;
        $this->max_size = $max_size * 1024 * 1024;
        $this->nametype = $nametype;
        $this->setPath($path);
    }

    public function setMaxSize($max_size = 64)
    {
        $this->max_size = $max_size;
    }

    public function setPath($path = "")
    {
        if ($path[strlen($path) - 1] != "/") {
            $path = $path . "/";
        }
        $this->path = $path;
    }

    public function uploadFileModel($model_name, $input_name)
    {
        if (isset($_FILES[$model_name])) {
            $input = $_FILES[$model_name];
            if (!empty($input)) {
                if (array_key_exists('name', $input)) {
                    if (array_key_exists($input_name, $input['name'])) {
                        if (gettype($input["name"][$input_name]) == "array") {
                            if ($input["name"][$input_name][0] != "") {
                                for ($x = 0; $x < count7($input["name"][$input_name]); $x++) {
                                    $name_img = $input["name"][$input_name][$x];
                                    $size = round($input['size'][$input_name][$x]);
                                    $tmp = $input['tmp_name'][$input_name][$x];
                                    $mime = strtolower(strrchr($name_img, "."));
                                    if ($this->nametype == "order") {
                                        $name = $x + 1 . $mime;
                                    } else if ($this->nametype == "timestamp") {
                                        $ran = rand(1, 1000000);
                                        $name = strtotime(date("Y-m-d H:i:s")) . "rand" . $ran . $mime;
                                    } else if ($this->nametype == "preserve") {
                                        $name = Formater::toUrl($input["name"][$input_name][$x], Formater::KEEP_FORMAT) . $mime;
                                    } else {
                                        $name = randid() . $mime;
                                    }
                                    if (in_array($mime, $this->allowed)) {
                                        if ($size < $this->max_size) {
                                            if (copy($tmp, $this->path . $name)) {
                                                $this->lastuploaded[] = array('status' => true, 'new' => $name, 'old' => $name_img, 'path' => $this->path . $name);
                                            } else {
                                                $this->lastuploaded[] = array('status' => false, 'msg' => "Falha ao enviar arquivo.", 'old' => $name_img);
                                            }
                                        } else {
                                            $this->lastuploaded[] = array('status' => false, 'msg' => "Imagem muito pesada.", 'old' => $name_img);
                                        }
                                    } else {
                                        $this->lastuploaded[] = array('status' => false, 'msg' => "Arquivo invalido.", 'old' => $name_img);
                                    }
                                }
                            }
                        } else {
                            $name_img = $input["name"][$input_name];
                            $size = round($input['size'][$input_name]);
                            $tmp = $input['tmp_name'][$input_name];
                            $mime = strtolower(strrchr($name_img, "."));
                            if ($this->nametype == "order") {
                                $name = 1 . $mime;
                                $y = 1;
                                while (file_exists($this->path . $name)) {
                                    $name = $y . $mime;
                                    $y++;
                                }
                            } else if ($this->nametype == "timestamp") {
                                $ran = rand(1, 1000000);
                                $name = strtotime(date("Y-m-d H:i:s")) . "rand" . $ran . $mime;
                            } else if ($this->nametype == "preserve") {
                                $name = $input["name"][$input_name];
                            } else {
                                $name = randid() . $mime;
                            }
                            if (in_array($mime, $this->allowed)) {
                                if ($size < $this->max_size) {
                                    if (copy($tmp, $this->path . $name)) {
                                        $this->lastuploaded[] = array('status' => true, 'new' => $name, 'old' => $name_img, 'path' => $this->path . $name);
                                    } else {
                                        $this->lastuploaded[] = array('status' => false, 'msg' => "Falha ao enviar arquivo.", 'old' => $name_img);
                                    }
                                } else {
                                    $this->lastuploaded[] = array('status' => false, 'msg' => "Imagem muito pesada.", 'old' => $name_img);
                                }
                            } else {
                                $this->lastuploaded[] = array('status' => false, 'msg' => "Arquivo invalido.", 'old' => $name_img);
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $this->lastuploaded;
    }

    public function uploadFile($input_name)
    {
        if (isset($_FILES[$input_name])) {
            $input = $_FILES[$input_name];
            if (!empty($input)) {
                if ($input["name"] != "") {
                    if (gettype($input["name"]) == "array") {
                        if ($input["name"][0] != "") {
                            for ($x = 0; $x < count7($input["name"]); $x++) {
                                $name_img = $input["name"][$x];
                                $size = round($input['size'][$x]);
                                $tmp = $input['tmp_name'][$x];
                                $mime = strtolower(strrchr($name_img, "."));
                                if ($this->nametype == "order") {
                                    $name = $x + 1 . $mime;
                                } else if ($this->nametype == "timestamp") {
                                    $ran = rand(1, 1000000);
                                    $name = strtotime(date("Y-m-d H:i:s")) . "rand" . $ran . $mime;
                                } else if ($this->nametype == "preserve") {
                                    $exp = explode('.', $input["name"][$x]);
                                    $ext = array_pop($exp);
                                    $name = Formater::toUrl(join('.', $exp), Formater::KEEP_FORMAT) . '.' . $ext;
                                    while (file_exists($this->path . $name)) {
                                        $name = Formater::toUrl(join('.', $exp), Formater::KEEP_FORMAT) . randid(4) . '.' . $ext;
                                    }
                                } else {
                                    $name = randid() . $mime;
                                }
                                if (in_array($mime, $this->allowed)) {
                                    if ($size < $this->max_size) {
                                        if (copy($tmp, $this->path . $name)) {
                                            $this->lastuploaded[] = array('status' => true, 'new' => $name, 'old' => $name_img, 'path' => $this->path . $name);
                                        } else {
                                            $this->lastuploaded[] = array('status' => false, 'msg' => "Falha ao enviar arquivo.", 'old' => $name_img);
                                        }
                                    } else {
                                        $this->lastuploaded[] = array('status' => false, 'msg' => "Imagem muito pesada.", 'old' => $name_img);
                                    }
                                } else {
                                    $this->lastuploaded[] = array('status' => false, 'msg' => "Arquivo invalido.", 'old' => $name_img);
                                }
                            }
                        }
                    } else {
                        $name_img = $input["name"];
                        $size = round($input['size']);
                        $tmp = $input['tmp_name'];
                        $mime = strtolower(strrchr($name_img, "."));
                        if ($this->nametype == "order") {
                            $name = 1 . $mime;
                            $y = 1;
                            while (file_exists($this->path . $name)) {
                                $name = $y . $mime;
                                $y++;
                            }
                        } else if ($this->nametype == "timestamp") {
                            $ran = rand(1, 1000000);
                            $name = strtotime(date("Y-m-d H:i:s")) . "rand" . $ran . $mime;
                        } else if ($this->nametype == "preserve") {
                            $exp = explode('.', $input["name"]);
                            $ext = array_pop($exp);
                            $name = Formater::toUrl(join('.', $exp), Formater::KEEP_FORMAT) . '.' . $ext;
                            while (file_exists($this->path . $name)) {
                                $name = Formater::toUrl(join('.', $exp), Formater::KEEP_FORMAT) . randid(4) . '.' . $ext;
                            }
                        } else {
                            $name = randid() . $mime;
                        }
                        if (in_array($mime, $this->allowed)) {
                            if ($size < $this->max_size) {
                                if (copy($tmp, $this->path . $name)) {
                                    $this->lastuploaded[] = array('status' => true, 'new' => $name, 'old' => $name_img, 'path' => $this->path . $name);
                                } else {
                                    $this->lastuploaded[] = array('status' => false, 'msg' => "Falha ao enviar arquivo.", 'old' => $name_img);
                                }
                            } else {
                                $this->lastuploaded[] = array('status' => false, 'msg' => "Imagem muito pesada.", 'old' => $name_img);
                            }
                        } else {
                            $this->lastuploaded[] = array('status' => false, 'msg' => "Arquivo invalido.", 'old' => $name_img);
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $this->lastuploaded;
    }

    public function removeTimestamp($removefactor)
    {
        $exp = explode(":", $removefactor);
        $words = array("y", "w", "d", "h", "i", "s");
        $grand = array("y" => 31556926, "w" => 604800, "d" => 86400, "h" => 3600, "i" => 60, "s" => 1);
        $vals = array();

        for ($x = 0; $x < count7($exp); $x++) {
            for ($y = 0; $y < count7($words); $y++) {
                if (strpos($exp[$x], $words[$y]) !== false) {
                    $exp[$x][strpos($exp[$x], $words[$y])] = "";
                    $vals[$words[$y]] = intval($exp[$x]);
                }
            }
        }
        $seg = 0;
        foreach ($vals as $key => $value) {
            $seg = $seg + ($grand[$key] * $value);
        }

        $scan = scandir($this->path);
        for ($x = 2; $x < count7($scan); $x++) {
            $exp = explode("rand", $scan[$x]);
            if (count7($exp) > 1) {
                if ($this->getFileTime($exp[0]) > $seg) {
                    unlink($this->path . "/" . $scan[$x]);
                }
            }
        }

    }

    private function getFileTime($timestamp)
    {
        $now = time();
        $time = (int)$now - $timestamp;
        return $time;
    }

}