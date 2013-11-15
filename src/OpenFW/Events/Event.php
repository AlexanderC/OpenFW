<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 2:35 PM
 */

namespace OpenFW\Events;


class Event
{
    /**
     * @var \stdClass
     */
    protected $storage;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var bool
     */
    protected $stopPropagation = false;

    /**
     * @param string $event
     * @param mixed $data
     */
    public function __construct($event, $data)
    {
        $this->event = $event;
        $this->data = $data;
        $this->storage = new \stdClass();
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return !$this->stopPropagation;
    }

    /**
     * @return void
     */
    public function stopPropagation()
    {
        $this->stopPropagation = true;
    }

    /**
     * @return void
     */
    public function keepPropagating()
    {
        $this->stopPropagation = false;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \stdClass
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->event;
    }
} 