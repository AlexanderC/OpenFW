<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:16 AM
 */

$apacheEnv = function_exists('apache_getenv') ? apache_getenv('OPENFW_ENV') : false;
define('OPENFW_ENV', $apacheEnv ? : 'dev');

require __DIR__ . "/../app/bootstrap.php";

$app->run()->send();