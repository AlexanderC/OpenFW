<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/11/13
 * @time 9:37 AM
 */

namespace OpenFW\Routing\Validator;


class RegexValidator extends AbstractValidator
{
    const DELIMITER = '#';
    const REGEX_TPL = "#^%s$#u";

    /**
     * @param string $argument
     * @return bool
     */
    public function validate($argument)
    {
        return (bool) preg_match(sprintf(self::REGEX_TPL, $this->expression), $argument);
    }

} 