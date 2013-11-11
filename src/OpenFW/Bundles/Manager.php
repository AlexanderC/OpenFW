<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 1:19 PM
 */

namespace OpenFW\Bundles;


use OpenFW\Bundles\Exception\BundleNotFoundException;

class Manager
{
    const BUNDLE_TRAIT = "OpenFW\\Traits\\Bundle";
    const CONTAINER_AWARE_TRAIT = "OpenFW\\Traits\\ContainerAware";

    /**
     * @var array
     */
    protected $bundles;

    /**
     * @param array $bundles
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * @return \Generator
     * @throws \RuntimeException
     * @throws \OpenFW\Exception\ConfigurationException
     */
    public function getBundles()
    {
        foreach($this->bundles as $name => $bundle) {
            $bundle['name'] = $name;

            $this->valueOrDefault('lazy', $bundle);
            $this->valueOrDefault('data', $bundle, null);

            yield $bundle;
        }
    }

    /**
     * @param array $bundle
     * @param \Pimple $container
     * @return mixed
     * @throws \RuntimeException
     */
    public function createBundleInstance(array $bundle, \Pimple $container)
    {
        $class = $bundle['class'];

        if(!class_exists($class)) {
            throw new BundleNotFoundException("Unable to find class {$class}");
        }

        $traits = class_uses($class);

        if(!in_array(self::BUNDLE_TRAIT, $traits)) {
            throw new \RuntimeException(sprintf("You must use %s trait in each bundle", self::BUNDLE_TRAIT));
        }

        $instance = new $class();

        $instance->setData($bundle['data']);

        if(in_array(self::CONTAINER_AWARE_TRAIT, $traits)) {
            $instance->setContainer($container);
        }

        $instance->checkEnvironment();

        return $instance;
    }

    /**
     * @param string $key
     * @param array $bundle
     * @param mixed $default
     */
    protected function valueOrDefault($key, array & $bundle, $default = false)
    {
        $bundle[$key] = array_key_exists($key, $bundle) ? $bundle[$key] : $default;
    }
} 