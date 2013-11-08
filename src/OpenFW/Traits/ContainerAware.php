<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:15 PM
 */

namespace OpenFW\Traits;


trait ContainerAware
{
    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * @param \Pimple $container
     */
    public function setContainer(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }
} 