<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:16 AM
 */

define('OPENFW_ENV', 'dev');

require __DIR__ . "/../app/bootstrap.php";

$app->run();
exit('ok');