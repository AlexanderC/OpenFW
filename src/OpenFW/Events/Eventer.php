<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:32 PM
 */

namespace OpenFW\Events;


use OpenFW\Events\Matchers\AbstractMatcher;
use OpenFW\Traits\ContainerAware;

class Eventer
{
    const DEFAULT_PRIORITY = -1;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @param string $event
     * @throws \UnexpectedValueException
     */
    public function register($event)
    {
        if($this->exists($event)) {
            throw new \UnexpectedValueException("Event {$event} already registered");
        }

        $this->events[$event] = $this->createQueue();
    }

    /**
     * @param string $event
     * @return bool
     */
    public function exists($event)
    {
        return isset($this->events[$event]);
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->events);
    }

    /**
     * @param AbstractMatcher $matcher
     * @param callable $listener
     * @param int $priority
     * @throws \OutOfBoundsException
     */
    public function addListener(AbstractMatcher $matcher, callable $listener, $priority = self::DEFAULT_PRIORITY)
    {
        /** @var $queue \SplPriorityQueue */
        foreach($this->events as $event => $queue) {
            if($matcher->match($event)) {
                $queue->insert($listener, $priority);
            }
        }
    }

    /**
     * @param string $event
     * @return bool
     * @throws \OutOfBoundsException
     */
    public function hasListeners($event)
    {
        if(!$this->exists($event)) {
            throw new \OutOfBoundsException("Event {$event} is not registered");
        }

        return !$this->events[$event]->isEmpty();
    }

    /**
     * @param string $event
     * @return \Generator
     * @throws \OutOfBoundsException
     */
    public function getListeners($event)
    {
        if(!$this->exists($event)) {
            throw new \OutOfBoundsException("Event {$event} is not registered");
        }

        foreach($this->events[$event] as $listener) {
            yield $listener;
        }
    }

    /**
     * @param string $event
     * @throws \OutOfBoundsException
     */
    public function flushListeners($event)
    {
        if(!$this->exists($event)) {
            throw new \OutOfBoundsException("Event {$event} is not registered");
        }

        $this->events[$event] = $this->createQueue();
    }

    /**
     * @param string $event
     * @param mixed $data
     * @throws \OutOfBoundsException
     */
    public function trigger($event, $data)
    {
        if(!$this->exists($event)) {
            throw new \OutOfBoundsException("Event {$event} is not registered");
        }

        $eventObject = new Event($event, $data);

        foreach($this->getListeners($event) as $listener) {
            call_user_func($listener, $eventObject);

            if(!$eventObject->isRunning()) {
                break;
            }
        }
    }

    /**
     * @return \SplPriorityQueue
     */
    protected function createQueue()
    {
        $queue = new \SplPriorityQueue();
        $queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        return $queue;
    }
} 