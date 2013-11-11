<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/11/13
 * @time 9:36 AM
 */

namespace OpenFW\Routing\Validator;


abstract class AbstractValidator
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
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $argument
     * @return bool
     */
    abstract public function validate($argument);
} 