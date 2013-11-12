<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:12 AM
 */

$app = call_user_func(function() {
    define('OPENFW_ROOT', realpath(__DIR__ . '/../'));

    $autoloadFile = OPENFW_ROOT . '/vendor/autoload.php';

    if(!is_file($autoloadFile)) {
        throw new \RuntimeException("You must do 'composer install' first.");
    }

    require OPENFW_ROOT . '/vendor/autoload.php';

    $installationChecker = new \OpenFW\CheckInstallation();
    $installationChecker->allCached();

    return \OpenFW\Application::create();
});
