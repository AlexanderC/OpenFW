<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 2:44 PM
 */

namespace OpenFW\Events\Matchers;


abstract class AbstractMatcher
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param string $event
     * @return bool
     */
    abstract public function match($event);
} 