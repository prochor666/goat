<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function _devPHPServerAccessLogger($httpStatus = 'Unknown')
{
    $dateTime = date('Y-m-d H:i:s');
    $mnth = date('Y-m');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $requestMethod = requestMethod();
    file_put_contents(__DIR__."/temp/access-log-dev-{$mnth}.log", "{$dateTime}\t{$requestMethod}\t{$httpStatus}\t{$_SERVER['REQUEST_URI']}\t{$userAgent}\n", FILE_APPEND);
}


function requestMethod()
{
    $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD']: 'UNKNOWN';
    return $method;
}


function isOptions()
{
    $method = isset($_SERVER['REQUEST_METHOD']) ? mb_strtolower($_SERVER['REQUEST_METHOD']): 'get';

    return $method === 'options' ? true: false;
}


$httpStatus = "HTTP/1.1 200 OK";

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept");
//header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, PATCH, DELETE");

if (isOptions()) {

    $httpStatus = "HTTP/1.1 204 No Content";
    header($httpStatus);
    _devPHPServerAccessLogger($httpStatus);

    die();
    return false;
}

if ($uri !== '/' && preg_match('/\.(?:php)$/', $uri)) {

    $httpStatus = "HTTP/1.1 406 Not Acceptable";
    _devPHPServerAccessLogger($httpStatus);
    header($httpStatus);
    die();
    return false;
}

$fsPath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, dirname($uri)) . DIRECTORY_SEPARATOR . urlencode(basename($uri));

//echo $fsPath;

if ($uri !== '/' && file_exists($fsPath)) {

    if (is_dir($fsPath)) {

        $httpStatus = "HTTP/1.1 204 No Content";
        _devPHPServerAccessLogger($httpStatus);
        header($httpStatus);
        die();
    }

    if (!preg_match('/\.(?:ico|png|jpg|jpeg|gif|bmp|wbmp|webp|html|html|xml|pls|m3u8|js|css|scss|txt|vaw|mp3|mp4|m4v)$/', $fsPath)) {

        _devPHPServerAccessLogger($httpStatus);

        $_quoted = sprintf('"%s"', addcslashes(basename(__DIR__."{$uri}"), '"\\'));
        $_size   = filesize(__DIR__."{$uri}");

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $_quoted);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $_size);
        readfile($fsPath);
        die();
    }

    _devPHPServerAccessLogger($httpStatus);
    return false;
}

_devPHPServerAccessLogger($httpStatus);

require_once __DIR__.'/index.php';
