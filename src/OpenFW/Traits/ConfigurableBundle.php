<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/11/13
 * @time 4:04 PM
 */

namespace OpenFW\Traits;


use OpenFW\Configuration\BundleAutoConfigurator;

trait ConfigurableBundle
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Parse bundle configuration
     *
     * @return void
     */
    public function parseConfig()
    {
        $configurator = new BundleAutoConfigurator($this);
        $configurator->parse();

        $this->config = $configurator->getConfig();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
} 