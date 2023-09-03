<?php

$DIR = 'preview';
if (!file_exists($DIR)) {
    @mkdir($DIR);
}

$cleanupTargetDir = true; 
$maxFileAge = 5 * 3600; 

if ($cleanupTargetDir) {
    if (!is_dir($DIR) || !$dir = opendir($DIR)) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
    }

    while (($file = readdir($dir)) !== false) {
        $tmpfilePath = $DIR . DIRECTORY_SEPARATOR . $file;

        if (@filemtime($tmpfilePath) < time() - $maxFileAge) {
            @unlink($tmpfilePath);
        }
    }
    closedir($dir);
}

$src = file_get_contents('php://input');

if (preg_match("#^data:image/(\w+);base64,(.*)$#", $src, $matches)) {

    $previewUrl = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI']
    );
    $previewUrl = str_replace("preview.php", "", $previewUrl);


    $base64 = $matches[2];
    $type = $matches[1];
    if ($type === 'jpeg') {
        $type = 'jpg';
    }

    $filename = md5($base64).".$type";
    $filePath = $DIR.DIRECTORY_SEPARATOR.$filename;

    if (file_exists($filePath)) {
        die('{"jsonrpc" : "2.0", "result" : "'.$previewUrl.'preview/'.$filename.'", "id" : "id"}');
    } else {
        $data = base64_decode($base64);
        file_put_contents($filePath, $data);
        die('{"jsonrpc" : "2.0", "result" : "'.$previewUrl.'preview/'.$filename.'", "id" : "id"}');
    }

} else {
    die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "un recoginized source"}}');
}