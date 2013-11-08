<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:22 PM
 */

namespace OpenFW\Traits;


trait Bundle
{
    /**
     * Check working environment to pass
     * bundle requirements
     *
     * @throws \RuntimeException
     */
    public function checkEnvironment() {
        throw new \RuntimeException("You must implement checkEnvironment method");
    }

    /**
     * Initialize bundle
     *
     * @throws \RuntimeException
     */
    public function init() {
        throw new \RuntimeException("You must implement init method");
    }
}