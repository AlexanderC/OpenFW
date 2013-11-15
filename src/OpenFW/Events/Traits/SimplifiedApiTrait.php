<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/15/13
 * @time 4:05 PM
 */

namespace OpenFW\Events\Traits;


use OpenFW\Events\Eventer;
use OpenFW\Events\Matchers\AbstractMatcher;

trait SimplifiedApiTrait
{
    /**
     * @see Eventer::register
     */
    public function add($event)
    {
        $this->register($event);
    }

    /**
     * @see Eventer::exists
     */
    public function is($event)
    {
        return $this->exists($event);
    }

    /**
     * @see Eventer::getEvents
     */
    public function all()
    {
        return $this->getEvents();
    }

    /**
     * @see Eventer::addListener
     */
    public function on(AbstractMatcher $matcher, callable $listener, $priority = Eventer::DEFAULT_PRIORITY)
    {
        $this->addListener($matcher, $listener, $priority);
    }

    /**
     * @see Eventer::addOnceListener
     */
    public function once(AbstractMatcher $matcher, callable $listener, $priority = Eventer::DEFAULT_PRIORITY)
    {
        $this->addOnceListener($matcher, $listener, $priority);
    }

    /**
     * @see Eventer::hasListeners
     */
    public function has($event)
    {
        return $this->hasListeners($event);
    }

    /**
     * @see Eventer::getListeners
     */
    public function get($event)
    {
        return $this->getListeners($event);
    }

    /**
     * @see Eventer::flushListeners
     */
    public function flush($event)
    {
        $this->flushListeners($event);
    }

    /**
     * @see Eventer::triggerUsingMatcher
     */
    public function triggerBatch(AbstractMatcher $matcher, $data = null)
    {
        return $this->triggerUsingMatcher($matcher, $data);
    }
} 