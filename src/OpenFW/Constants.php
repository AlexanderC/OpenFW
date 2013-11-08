<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:35 AM
 */

namespace OpenFW;


use OpenFW\Constants\Containers;
use OpenFW\Constants\Environment;
use OpenFW\Constants\Services;
use OpenFW\Constants\SystemEvents;
use OpenFW\Constants\WorkingDirectories;

class Constants implements
    WorkingDirectories,
    Services,
    Containers,
    SystemEvents,
    Environment
{
    /**
     * @param string $relativePath
     * @return string
     * @throws \RuntimeException
     */
    public static function getResolvedPath($relativePath)
    {
        return sprintf("%s/%s", self::getRootDirectory(), $relativePath);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public static function getRootDirectory()
    {
        $rootDir = defined('OPENFW_ROOT') ? OPENFW_ROOT : realpath(__DIR__ . '/../../');

        if(false === $rootDir) {
            throw new \RuntimeException("Unable to resolve project root directory.");
        }

        return $rootDir;
    }
}