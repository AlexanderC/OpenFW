<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/11/13
 * @time 3:47 PM
 */

namespace OpenFW\Configuration;


use OpenFW\Bundles\Manager;
use OpenFW\Constants;

class BundleAutoConfigurator extends BundleConfigurator
{
    const CONFIG_DIRECTORY_TPL = "%s/config";

    /**
     * @param object $bundle
     * @throws \RuntimeException
     */
    public function __construct($bundle)
    {
        $traits = class_uses($bundle);

        if(!in_array(Manager::BUNDLE_TRAIT, $traits)
            || !in_array(Manager::CONTAINER_AWARE_TRAIT, $traits)) {
            throw new \RuntimeException(
                sprintf("You may provide a valid bundle that uses %s trait", Manager::CONTAINER_AWARE_TRAIT)
            );
        }

        $directory = sprintf(self::CONFIG_DIRECTORY_TPL, $bundle->getDirectory());

        parent::__construct(
            $bundle->getContainer()[Constants::ENVIRONMENT_SERVICE]->getEnvironment(),
            $directory
        );
    }
} 