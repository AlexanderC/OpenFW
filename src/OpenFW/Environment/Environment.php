<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:30 PM
 */

namespace OpenFW\Environment;


use OpenFW\Constants;

class Environment
{
    /**
     * @var array
     */
    protected $vars;

    public function __construct()
    {
        $this->vars = $_ENV;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return defined('OPENFW_ENV') ? OPENFW_ENV : Constants::DEFAULT_ENV;
    }

    /**
     * @return array
     */
    public function getEnvVars()
    {
        return $this->vars;
    }

    /**
     * @return string
     */
    public function getSapiName()
    {
        return php_sapi_name();
    }

    /**
     * @return bool
     */
    public function isCli()
    {
        return $this->getSapiName() === 'cli';
    }
} 