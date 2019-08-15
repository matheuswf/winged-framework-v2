<?php

namespace Winged\Directory;

use Winged\File\File;
use Winged\Formater\Formater;
use Winged\Utils\WingedLib;

class Directory
{
    public $folder = null;

    public function __construct($folder, $forceCreate = true)
    {
        if (is_bool(stripos(DOCUMENT_ROOT, $folder))) {
            $folder = DOCUMENT_ROOT . $folder;
        }
        $folder = str_replace(DOCUMENT_ROOT . DOCUMENT_ROOT, DOCUMENT_ROOT, $folder);

        $folder_accent = $folder;

        $folder = explode('/', $folder);
        foreach ($folder as $key => $value) {
            $v = Formater::removeAccents(Formater::removeSpaces($value));
            if ($v != $folder) {
                $folder[$key] = $v;
            }
        }
        $folder = join('/', $folder);

        if ($folder_accent != $folder) {
            if (is_dir(utf8_decode($folder_accent)) && file_exists(utf8_decode($folder_accent))) {
                $this->folder = WingedLib::clearPath($folder_accent) . '/';
            }
        } else {
            if (is_dir($folder) && file_exists($folder)) {
                $this->folder = WingedLib::clearPath($folder) . '/';
            } else {
                if ($forceCreate && !$this->exists()) {
                    self::dynamicCreate($folder);
                }
            }
            if (is_dir($folder) && file_exists($folder)) {
                $this->folder = WingedLib::clearPath($folder) . '/';
            }
        }
    }

    /**
     * @param $folder
     */
    public static function dynamicCreate($folder)
    {
        $exp = explode('/', str_replace(DOCUMENT_ROOT, '', $folder));
        $folders = [];
        $first = true;
        $count = 0;
        foreach ($exp as $folder) {
            if ($folder != '' && $folder != '/') {
                if ($first) {
                    $first = false;
                    $folders[] = './' . $folder;
                } else {
                    $folders[] = $folders[$count] . '/' . $folder;
                    $count++;
                }
            }
        }

        foreach ($folders as $folder) {
            if (!file_exists($folder) && !is_dir($folder)) {
                mkdir($folder);
            }
        }

    }

    public static function clearDirectory($folder, $keepWithFilesExtensions = [])
    {
        $folder = WingedLib::normalizePath($folder);
        $scan = scandir($folder);
        if (is_string($keepWithFilesExtensions)) {
            $keepWithFilesExtensions = [$keepWithFilesExtensions];
        }
        foreach ($scan as $file) {
            if ($file != '.' && $file != '..') {
                $join_f = $folder . $file;
                if (is_dir($join_f)) {
                    self::clearDirectory($join_f, $keepWithFilesExtensions);
                    (new Directory($join_f))->delete();
                } else if (is_file($join_f) && file_exists($join_f)) {
                    $file = new File($join_f);
                    if (!in_array($file->getExtension(), $keepWithFilesExtensions)) {
                        $file->delete();
                    }
                }
            }
        }
    }

    public static function clearDirectoryDelete($folder, $keepWithFileExtensions = [])
    {
        try {
            self::clearDirectory($folder, $keepWithFileExtensions);
            (new Directory($folder))->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function readDir()
    {
        $files = [];
        if ($this->exists()) {
            $scan = scandir($this->folder);
            if ($scan && is_array($scan)) {
                array_shift($scan);
                array_shift($scan);
                foreach ($scan as $file) {
                    $file = new File($this->folder . $file, false);
                    if ($file->exists()) {
                        $files[] = $file;
                    }
                }
            }
        }
        return $files;
    }

    public function deleteFullPath()
    {
        if ($this->folder != null) {
            $exp = explode('/', $this->folder);
            $expCount = count7($exp);
            while ($expCount != 2) {
                array_pop($exp);
            }
            $folder = join('/', $exp) . '/';
            return self::clearDirectoryDelete($folder);
        }
        return false;
    }

    public function delete()
    {
        if ($this->folder != null) {
            if (file_exists($this->folder) && is_dir($this->folder)) {
                return rmdir($this->folder);
            }
        }
        return false;
    }

    public function exists()
    {
        if (is_dir($this->folder)) return $this;
        return false;
    }
}