<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/9/13
 * @time 12:27 AM
 */

namespace OpenFW\Configuration;


use OpenFW\Exception\ConfigurationException;

class Validator
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws ConfigurationException
     */
    public function validate()
    {
        $options = ['debug', 'bundles'/*, 'cache'*/];

        foreach($options as $option) {
            if(!array_key_exists($option, $this->config)) {
                throw new ConfigurationException("Unable to find '{$option}' options in environment configuration.");
            }
        }

        /*
        $cacheOptions = ['class', 'arguments'];

        foreach($cacheOptions as $option) {
            if(!array_key_exists($option, $this->config['cache'])) {
                throw new ConfigurationException(
                    "Unable to find 'cache.{$option}' options in environment configuration."
                );
            }
        }
        */
    }
} 