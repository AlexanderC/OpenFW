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
     * @var mixed
     */
    protected $data;

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check working environment to pass
     * bundle requirements
     *
     * @throws \RuntimeException
     */
    public function checkEnvironment()
    {
        throw new \RuntimeException("You must implement checkEnvironment method");
    }

    /**
     * Initialize bundle lazy mode first
     *
     * @throws \RuntimeException
     */
    public function initLazy()
    {
        throw new \RuntimeException("You must implement initLazy method");
    }

    /**
     * Initialize bundle
     *
     * @throws \RuntimeException
     */
    public function init()
    {
        throw new \RuntimeException("You must implement init method");
    }
}