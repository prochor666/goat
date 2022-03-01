<?php
/**
* Random generator
* @param int $length
* @param bool $numOnly
* @return int|string
*/
function rnd($length = 5, $numOnly = false)
{
    $args = $numOnly === true ? '0123456789': 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $str = null;

    while (strlen($str) < $length) {

        $str .= mb_substr($args, mt_rand(0, strlen($args) - 1), 1);
    }

    return (string)$str;
}


/**
* Hash wrapper
* @param string $str
* @param string $algo
* @return string
*/
function getHash($str, $algo = 'sha512')
{
    $algo = mb_strtolower($algo);

    // DES std, salted
    if (CRYPT_STD_DES == 1 && $algo == 'stddes') {

        return crypt($str, rnd(2));
    }

    // DES extended, salted
    if (CRYPT_EXT_DES == 1 && $algo == 'extdes') {

        return crypt($str, '_XZZZ'.rnd(4));
    }

    // Blowfish salted
    if (CRYPT_BLOWFISH == 1 && $algo == 'blowfish') {

        return crypt($str, '$2y$27$'.rnd(22).'$');
    }

    // SHA-512 salted
    if (CRYPT_SHA512 == 1 && $algo == 'sha512salt') {

        return crypt($str, '$6$rounds=30000$'.rnd(16).'$');
    }

    // SHA-256 salted
    if (CRYPT_SHA256 == 1 && $algo == 'sha256salt') {

        return crypt($str, '$5$rounds=15000$'.rnd(16).'$');
    }

    // SHA-512
    if (function_exists('hash') && in_array( 'sha512', hash_algos() ) && $algo == 'sha512' ) {

        return hash('sha512', $str);
    }

    // SHA-384
    if (function_exists('hash') && in_array( 'sha384', hash_algos() ) && $algo == 'sha384' ) {

        return hash('sha384', $str);
    }

    // SHA-256
    if (function_exists('hash') && in_array( 'sha256', hash_algos() ) && $algo == 'sha256' ) {

        return hash('sha256', $str);
    }

    // SHA-1
    if (function_exists('sha1') && $algo == 'sha1') {

        return sha1($str);
    }

    // CRC32
    if (function_exists('hash') && in_array( 'crc32', hash_algos() ) && $algo == 'crc32' ) {

        return hash('crc32', $str);
    }

    return md5($str);
}


/**
* Dump and continue
* @param mixed
* @return string
*/
function dump()
{
    return dumpList(func_num_args(), func_get_args());
}


/**
* Dump and die
* @param mixed
* @return void
*/
function dd()
{
    die(dumpList(func_num_args(), func_get_args()));
}


/**
* List function arguments
* @param int $n
* @param array $a
* @return string
*/
function dumpList($n, $a) {
    if ($n>0) {

        foreach ($a as $key => $var) {

            if (PHP_SAPI !== 'cli') {

                echo '<pre>';
            }

            var_dump($var);

            if (PHP_SAPI !== 'cli') {

                echo '</pre>';
            }
        }
    }else{

        echo PHP_SAPI === 'cli' ? 'DUMP: no-data': '<pre>DUMP: no-data</pre>';
    }

    $result = ob_get_clean();
    return $result;
}


/**
* Slice big array
* @param array $data
* @param int $from
* @param int $to
* @return array
*/
function slice($data=[], $from = 0, $to = 0)
{
    $newDataset = [];

    if (is_array($data) && count($data)>0 && $to > 0) {
        $newDataset = array_slice($data, $from, $to);
        unset($data);
    }

    return $newDataset;
}


/**
* Sort single array by length
* @param array $data
* @return array
*/
function sortByLength($data)
{
    usort ($data, function($a, $b) {

        return mb_strlen($b) - mb_strlen($a);
    });

    return $data;
}


/**
* Tests if string starts with another string
* @param string $path
* @param string|array $needle
* @return bool|string
*/
function startsWith($str = NULL, $needle = [])
{
    if (mb_strlen($str) === 0) {

        return false;
    }

    if (!is_array($needle)) {

        $needle = [$needle];
    }

    $needle = sortByLength($needle);

    foreach ($needle as $s) {

        if (mb_strlen($s)>0 && strpos($str, $s) === 0) {

            return $s;
        }
    }

    return false;
}


/**
* Tests if string ends with another string
* @param string $path
* @param string|array $needle
* @return bool|string
*/
function endsWith($str = NULL, $needle = [])
{
    if (mb_strlen($str) == 0) {

        return false;
    }

    if (!is_array($needle)) {
        $needle = [$needle];
    }

    $needle = sortByLength($needle);

    foreach ($needle as $s) {

        $l = mb_strlen($s);

        if (mb_strlen($s) > 0 && mb_substr($str, -mb_strlen($s)) == $s) {

            return $s;
        }
    }

    return false;
}


/**
* arrayKey alias
* @param array $a
* @param string $k as key value
* @param mixed $d as default value
* @return mixed
*/
function ark($a, $k, $d = false)
{
    return arrayKey($a, $k, $d);
}


/**
* Asociative array key | object property checker
* @param array|object $a
* @param string $k as key value
* @param mixed $d as default value
* @return mixed
*/
function arrayKey($a, $k, $d = false)
{
    if (is_object($a)) {

        $a = json_decode(json_encode($a), true);
    }

    return is_array($a) && array_key_exists($k, $a) ? $a[$k]: $d;
}


/**
* Web redirect
* @param string $url
* @return void
*/
function redirect($url = null)
{
    $url = !is_null($url) && is_string($url) && mb_strlen($url)>0 ? $url: false;

    if ($url !== false) {

        header("Location:".$url);
        exit();
    }
}


/**
* Sned custom HTTP status code
* @param string $headerStatus
*/
function headerStatus($headerStatus, $replace = false, $code = 0)
{
    if ($code > 0) {

        header($headerStatus, $replace, $code);
    }else{

        header($headerStatus, $replace);
    }
}


/**
* Get client IPv4/IPv6 address (proxies enabled)
* @return string
*/
function clientIp()
{
    $clientVars = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($clientVars as $key) {

        if (array_key_exists($key, $_SERVER) === true) {

            foreach (explode(',', $_SERVER[$key]) as $ip) {

                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {

                    return $ip;
                }
            }
        }
    }

    return null;
}


/**
* Detect SSL (proxies enabled)
* @return bool
*/
function ssl()
{
    return isset($_SERVER['HTTP_X_FORWARDED_SSL']) || isset($_SERVER['HTTPS']);
}


function requestDuration()
{
    return (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
}