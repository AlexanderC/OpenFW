<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:34 AM
 */

namespace OpenFW;


use OpenFW\Exception\InstallationException;

class CheckInstallation
{
    const CHECK_REGEXP = '#^check.+$#ui';

    public function __construct()
    {   }

    /**
     * @throws \RuntimeException
     */
    public function allCached()
    {
        $cacheFile = sprintf("%s/__check_install_cache.tmp", Constants::getResolvedPath(Constants::CACHE_DIR));

        if(!is_file($cacheFile)) {
            foreach(get_class_methods($this) as $method) {
                if(preg_match(self::CHECK_REGEXP, $method)) {
                    $this->{$method}();
                }
            }

            if(!@touch($cacheFile)) {
                throw new \RuntimeException("Unable to persist installation cache file.");
            }
        } else {
            $this->checkCrucial();
        }
    }

    /**
     * @return void
     */
    public function all()
    {
        foreach(get_class_methods($this) as $method) {
            if(preg_match(self::CHECK_REGEXP, $method)) {
                $this->{$method}();
            }
        }
    }

    /**
     * @throws Exception\InstallationException
     */
    public function checkEnvironment()
    {
        // check version
        if(!version_compare(PHP_VERSION, '5.5.0', '>=')) {
            throw new InstallationException(sprintf("PHP version 5.5.0 >= expected, installed %s", PHP_VERSION));
        }

        // check for loaded extensions
        $requiredExtension = [
            'SPL' => false,
            'hash' => false,
            'iconv' => false,
            'mbstring' => false
        ];

        foreach($requiredExtension as $extension => $requiredVersion) {
            if(false === extension_loaded($extension)) {
                throw new InstallationException("Extension {$extension} should be loaded.");
            } elseif(false !== $requiredVersion && !version_compare(phpversion($extension), $requiredVersion, '>=')) {
                throw new InstallationException("{$extension} version should be {$requiredVersion} >=.");
            }
        }

        // check for depending packages
        $packagesToExists = [
            "Pimple" => 'pimple/pimple',
            "Symfony\\Component\\HttpFoundation\\Request" => 'symfony/http-foundation',
        ];

        foreach($packagesToExists as $classToCheck => $package) {
            if(!class_exists($classToCheck)) {
                throw new InstallationException(
                    "Unable to find '{$classToCheck}' class. Check if '{$package}' package loaded."
                );
            }
        }
    }

    /**
     * @throws Exception\InstallationException
     */
    public function checkCrucial()
    {
        $writeableDirectories = [
            Constants::CACHE_DIR,
            Constants::LOGS_DIR,
        ];

        foreach($writeableDirectories as $relativePath) {
            $dir = Constants::getResolvedPath($relativePath);
            if(!is_writeable($dir)) {
                throw new InstallationException("Directory {$dir} should be writeable.");
            }
        }
    }

    /**
     * @throws Exception\InstallationException
     */
    public function checkWorkingDirectories()
    {
        $readableDirectories = [
            Constants::APP_DIR,
            Constants::BIN_DIR,
            Constants::CACHE_DIR,
            Constants::CONFIG_DIR,
            Constants::LOGS_DIR,
            Constants::SRC_DIR,
            Constants::TESTS_DIR,
            Constants::VENDOR_DIR,
            Constants::WEB_DIR
        ];

        $writeableDirectories = [
            Constants::CACHE_DIR,
            Constants::LOGS_DIR,
        ];

        foreach($readableDirectories as $relativePath) {
            $dir = Constants::getResolvedPath($relativePath);
            if(!is_readable($dir)) {
                throw new InstallationException("Directory {$dir} should be readable.");
            }
        }

        foreach($writeableDirectories as $relativePath) {
            $dir = Constants::getResolvedPath($relativePath);
            if(!is_writeable($dir)) {
                throw new InstallationException("Directory {$dir} should be writeable.");
            }
        }
    }
} 