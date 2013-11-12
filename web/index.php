<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:16 AM
 */

// case developer accesses his app on localhost
if(true === ($isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', "::1"]))) {
    define('__PROFILER_ENABLED', true);
    require_once __DIR__ . "/profiler.php";
}

$apacheEnv = function_exists('apache_getenv') ? apache_getenv('OPENFW_ENV') : false;
define('OPENFW_ENV', $apacheEnv ? : 'dev');

require __DIR__ . "/../app/bootstrap.php";

// always enable errors reporting
// when developer connected
if($isLocal) {
    error_reporting(E_ALL);
}

$app->run()->send();

