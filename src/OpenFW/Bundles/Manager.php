<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 1:19 PM
 */

namespace OpenFW\Bundles;


use OpenFW\Bundles\Exception\BundleNotFoundException;
use OpenFW\Configuration\Configurator;
use OpenFW\Exception\ConfigurationException;

class Manager
{
    const BUNDLE_TRAIT = "OpenFW\\Traits\\Bundle";
    const CONTAINER_AWARE_TRAIT = "OpenFW\\Traits\\ContainerAware";

    /**
     * @var \OpenFW\Configuration\Configurator
     */
    protected $configurator;

    /**
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @return \Generator
     * @throws \RuntimeException
     * @throws \OpenFW\Exception\ConfigurationException
     */
    public function getBundles()
    {
        foreach($this->configurator->getConfig()['bundles'] as $name => $bundle) {
            if(!isset($bundle['class'])) {
                throw new ConfigurationException("Bundle section must have 'class' option");
            }

            $bundle['name'] = $name;
            $bundle['lazy'] = array_key_exists('lazy', $bundle) ? $bundle['lazy'] : false;

            yield $bundle;
        }
    }

    /**
     * @param string $class
     * @param \Pimple $container
     * @return mixed
     * @throws \RuntimeException
     */
    public function createBundleInstance($class, \Pimple $container)
    {
        if(!class_exists($class)) {
            throw new BundleNotFoundException("Unable to find class {$class}");
        }

        $traits = class_uses($class);

        if(!in_array(self::BUNDLE_TRAIT, $traits)) {
            throw new \RuntimeException(sprintf("You must use %s trait in each bundle", self::BUNDLE_TRAIT));
        }

        $instance = new $class();

        if(in_array(self::CONTAINER_AWARE_TRAIT, $traits)) {
            $instance->setContainer($container);
        }

        return $instance;
    }
} 