<?php
    if(!function_exists('parse_file'))
    {
        function parse_file($file, $line)
        {
            return basename($file)."line {$line}";
        }
    }
    $main = $message;
    $arr = array(
        'code' => 0,
        'errormsg' => $message,
        'file' => $file,
        'line' => $line);
        $json = str_replace("\\/", "/",json_encode($arr,JSON_UNESCAPED_UNICODE));
        echo $json;

?>

