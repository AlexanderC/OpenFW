<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/11/13
 * @time 10:06 PM
 */

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use OpenFW\Constants;

$sysLog = new SyslogHandler('OpenFW');
$logStream = new RotatingFileHandler(sprintf("%s/log.txt", Constants::getResolvedPath(Constants::LOGS_DIR)), 10);

return [
    'dev' => [
        'channels' => [
            'OpenFW' => [$logStream, $sysLog]
        ]
    ],
    'prod' => [
        'channels' => [
            'OpenFW' => [$logStream, $sysLog]
        ]
    ]
];