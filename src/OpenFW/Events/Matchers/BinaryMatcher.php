<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 2:54 PM
 */

namespace OpenFW\Events\Matchers;


class BinaryMatcher extends AbstractMatcher
{
    /**
     * @param string $event
     * @return bool
     */
    public function match($event)
    {
        return $event === $this->expression;
    }

} 