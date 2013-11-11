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
        $options = ['debug', 'bundles'];

        // validate available options
        foreach($options as $option) {
            if(!array_key_exists($option, $this->config)) {
                throw new ConfigurationException("Unable to find '{$option}' options in environment configuration.");
            }
        }

        if(!is_array($this->config['bundles'])) {
            throw new ConfigurationException("Bundle section must be an array.");
        }

        // validate bundles section
        foreach($this->config['bundles'] as $bundle) {
            if(!is_array($bundle)) {
                throw new ConfigurationException("Named Bundle section must be an array.");
            }

            if(!isset($bundle['class'])) {
                throw new ConfigurationException("Named Bundle section must have 'class' option.");
            }
        }
    }
} 