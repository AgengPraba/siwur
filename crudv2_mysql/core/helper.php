<?php

function safe($str)
{
    return strip_tags(trim($str));
}

function readJSON($path)
{
    $string = file_get_contents($path);
    $obj = json_decode($string);
    return $obj;
}

function createFile($string, $path)
{
    //echo $path; die();
   // chmod($path, 0777);
    $create = fopen($path, "w") or die("Change your permision folder $path for application and harviacode folder to 777");
    fwrite($create, $string);
    fclose($create);
    chmod($path, 0777);

    return $path;
}

function label($str)
{
    $label = str_replace('_', ' ', $str);
    $label = ucwords($label);
    return $label;
}
