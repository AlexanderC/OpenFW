<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:32 PM
 */

namespace OpenFW\Events;


use OpenFW\Events\Matchers\AbstractMatcher;
use OpenFW\Events\Traits\SimplifiedApiTrait;

/**
 * Class Eventer
 * @package OpenFW\Events
 *
 * @example
 * use OpenFW\Events\Eventer;
 * use OpenFW\Events\Event;
 * use OpenFW\Events\Matchers\BinaryMatcher;
 * use OpenFW\Events\Matchers\RegexMatcher;
 * use OpenFW\Events\Traits\SimplifiedApiTrait;
 *
 * $eventer = new Eventer();
 *
 * $events = [
 *      'foo.bar.event',
 *      'foo.baz.smth',
 *      'foo.habra.event',
 *      'smth.habra.post'
 * ];
 *
 * foreach($events as $event) {
 *      $eventer->register($event);
 * }
 *
 * echo "Adding some listeners\n";
 *
 * $eventer->addListener(new BinaryMatcher('foo.habra.event'), function(Event $event) {
 *      echo sprintf("This will be called on %s event only\n", $event);
 * });
 *
 * $eventer->addListener(new RegexMatcher('.+\.habra\..+'), function(Event $event) {
 *      echo sprintf("Wow, calling habra events! (%s)\n", $event);
 * });
 *
 * $eventer->addOnceListener(new RegexMatcher('foo\..+\.event'), function(Event $event) {
 *      echo sprintf("This event is one of [foo.bar.event, foo.habra.event] -> %s. ", $event),
 *              "Also this is thrown only once!\n";
 * });
 *
 * echo "Trigger all events once using binary matcher\n";
 * foreach($events as $event) {
 *      $eventer->trigger($event, ['some', 'data', 'provided', 'to', 'each', 'listener']);
 * }
 *
 * echo "Trigger all events that matches against an RegexMatcher\n";
 * $eventer->triggerUsingMatcher(
 *              new RegexMatcher('foo\..+\.event'),
 *              ['some', 'data', 'provided', 'to', 'each', 'listener']
 * );
 *
 * // too much words??? check SimplifiedApiTrait...
 */
class Eventer
{
    use SimplifiedApiTrait;

    const DEFAULT_PRIORITY = -0x001;

    /**
     * Events -> Listeners hashmap
     *
     * @var array
     */
    protected $events = [];

    /**
     * Add a new event
     *
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
     * Check whether event exists
     *
     * @param string $event
     * @return bool
     */
    public function exists($event)
    {
        return isset($this->events[$event]);
    }

    /**
     * Get all available events
     *
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->events);
    }

    /**
     * Add event listener for all
     * events that matches against
     * given matcher
     *
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
     * Add event listener for all
     * events that matches against
     * given matcher.
     *
     * Note that this listener will run only once!
     *
     * @param AbstractMatcher $matcher
     * @param callable $listener
     * @param int $priority
     */
    public function addOnceListener(AbstractMatcher $matcher, callable $listener, $priority = self::DEFAULT_PRIORITY)
    {
        // ensure listener would be run only once
        $onceListener = function(Event $event) use ($listener) {
            static $run = false;

            if(!$run) {
                return call_user_func($listener, $event);
                $run = true;
            }
        };

        $this->addListener($matcher, $onceListener, $priority);
    }

    /**
     * Check whether event has any listeners
     *
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
     * Get all registered event listeners
     *
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
     * Remove all registered event listeners
     *
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
     * Trigger an event.
     * Event name matches
     * using biary("===") matcher
     *
     * @param string $event
     * @param mixed $data
     * @throws \OutOfBoundsException
     */
    public function trigger($event, $data = null)
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
     * Trigger a set of events that
     * matches against given matcher
     *
     * @param AbstractMatcher $matcher
     * @param mixed $data
     */
    public function triggerUsingMatcher(AbstractMatcher $matcher, $data = null)
    {
        /** @var $queue \SplPriorityQueue */
        foreach($this->events as $event => $queue) {
            if($matcher->match($event)) {
                $this->trigger($event, $data);
            }
        }
    }

    /**
     * Create SPL priority queue instance
     * with EXTR_DATA flag set
     *
     * @return \SplPriorityQueue
     */
    protected function createQueue()
    {
        $queue = new \SplPriorityQueue();
        $queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        return $queue;
    }
} 