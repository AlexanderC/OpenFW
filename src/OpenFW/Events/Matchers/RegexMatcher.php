<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 2:55 PM
 */

namespace OpenFW\Events\Matchers;


class RegexMatcher extends AbstractMatcher
{
    const DELIMITER = '#';

    /**
     * @param string $event
     * @return bool
     */
    public function match($event)
    {
        return (bool) preg_match(sprintf("%s^%s$%su", self::DELIMITER, $this->expression, self::DELIMITER), $event);
    }

} 