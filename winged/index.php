<?php

$htaccess = "#Don't erase this line <Winged>#

RewriteEngine On

## Only for production environment ##
    
    # HTTPS Force #    
        #RewriteCond %{HTTPS} off
        #RewriteRule ^ https://<replace_with_your_domain_name>%{REQUEST_URI} [R=301,L]
    # End HTTPS Force #
    
    # WWW Force #    
        #RewriteCond %{HTTP_HOST} (?!^www\.)^(.+)$ [OR]
        #RewriteRule ^ https://<replace_with_your_domain_name>%{REQUEST_URI} [R=301,L]
    # End HTTPS Force #
    
    # HTTPS and WWW Force #    
        #RewriteCond %{HTTPS_HOST} (?!^www\.)^(.+)$ [OR]
        #RewriteCond %{HTTPS} off
        #RewriteRule ^ https://www.<replace_with_your_domain_name>%{REQUEST_URI} [R=301,L]
    # End HTTPS Force #
    
## End Helpers URL Normalize ##
    
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
#If you are developing the site in a local environment with a folder and subfolders scheme such as
#192.168.25.2/project_name/www/ being your root. When uploading the project configure the path for proper rewrite.
RewriteRule .(/)?$ '<replace>index.php'";

$index = "<?php
#Don't erase this line <Winged>#
define(\"DOCUMENT_ROOT\", str_replace(\"\\". '\\' ."\", \"/\", dirname(__FILE__) . \"/\"));
include_once \"./winged/classes/winged.class.php\";
Winged::start();";

winged($htaccess, $index);

function winged($htaccess, $index)
{
    $paths = array(
        "index" => "../index.php",
        "htaccess" => "../.htaccess"
    );
    $self = $_SERVER["PHP_SELF"];
    $exp = explode("/", $self);
    $path = "";
    for ($x = 1; $x < count($exp) - 2; $x++) {
        if ($x == 1) {
            $path = $exp[$x];
        } else {
            $path .= "/" . $exp[$x];
        }
    }
    $path .= "/";

    if (file_exists($paths["htaccess"])) {
        $htaccess_e = file_get_contents($paths["htaccess"]);
        if (find_reserved_word($htaccess_e)) {
            if (save_old_file($paths["htaccess"], $htaccess_e)) {
                $htaccess = str_replace("<replace>", $path, $htaccess);
                $paths["htaccess"] = save_file($paths["htaccess"], $htaccess);
            }
        }
    } else {
        $htaccess = str_replace("<replace>", $path, $htaccess);
        $paths["htaccess"] = save_file($paths["htaccess"], $htaccess);
    }


    if (file_exists($paths["index"])) {
        $index_e = file_get_contents($paths["index"]);
        if (find_reserved_word($index_e)) {
            if (save_old_file($paths["index"], $index_e)) {
                $paths["index"] = save_file($paths["index"], $index);
            }
        }
    } else {
        $paths["index"] = save_file($paths["index"], $index);
    }

    header("Location: ./");
}

function find_reserved_word($string)
{
    $pos = strpos($string, "<Winged>");
    if ($pos !== false) {
        return false;
    } else {
        return true;
    }
}

function save_file($file, $string)
{
    try {
        return file_put_contents($file, $string);
        /*$handle = fopen($file, "w+");
        fwrite($handle, $string);
        fclose($handle);
        @chmod($file, '0755');
        return true;*/
    } catch (Exception $e) {
        return $e;
    }
}

function save_old_file($file, $string)
{
    $exp = explode("/", $file);
    $exp[count($exp) - 1] = "old_" . $exp[count($exp) - 1];
    $f = implode("/", $exp);
    try {
        $handle = fopen($f, "w+");
        fwrite($handle, $string);
        fclose($handle);
        return true;
    } catch (Exception $e) {
        return $e;
    }
}
