<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 6:29 PM
 */

namespace OpenFW\Routing;

/**
 * Class Route
 * @package OpenFW\Routing
 *
 * /posts/{ author}/show/{postSlug   }
 */
class Route
{
    const PARAMETER_REGEX = '#(?:(?:^|/))?(\s*{\s*([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*}\s*)(?:(?:/)|$)#u';
    const MATCH_PARAMETER_REPLACEMENT = "/(?<\\2>[^/]*)/";
    const REVERSE_PARAMETER_REPLACEMENT = "/%s/";
    const REGEX_TPL = "#^%s$#u";

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var callable
     */
    protected $controller;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $matchRegex;

    /**
     * @var string
     */
    protected $reversedTemplate;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @param string $expression
     * @param callable $controller
     */
    public function __construct($expression, callable $controller)
    {
        $this->controller = $controller;
        $this->expression = sprintf("/%s", ltrim(trim($expression), "/ "));

        $this->parse();
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param string $parameter
     * @return string
     * @throws \RuntimeException
     */
    public function getValidator($parameter)
    {
        if(!$this->hasParameter($parameter)) {
            throw new \RuntimeException("Parameter {$parameter} does not exists.");
        }

        return $this->validators[$parameter];
    }

    /**
     * @param string $parameter
     * @param string $validator
     * @return $this
     * @throws \RuntimeException
     */
    public function setValidator($parameter, $validator)
    {
        if(!$this->hasParameter($parameter)) {
            throw new \RuntimeException("Parameter {$parameter} does not exists.");
        }

        $this->validators[$parameter] = $validator;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParameter($name)
    {
        return in_array($name, $this->parameters);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * We need this basically for wakeup only
     *
     * @param callable $controller
     * @return $this
     */
    public function setController(callable $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return callable
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param array $parameters
     * @return mixed
     * @throws \RuntimeException
     */
    public function generate(array $parameters = [])
    {
        $diff = array_diff($this->parameters, array_keys($parameters));

        if(count($diff) > 0) {
            throw new \RuntimeException(
                sprintf("Missing route named parameters: %s", trim(implode(", ", $diff), " ,"))
            );
        }

        foreach($this->validators as $name => $validator) {
            if(!preg_match(sprintf(self::REGEX_TPL, $validator), $parameters[$name])) {
                throw new \RuntimeException(
                    sprintf("Unable to validate '%s' using '%s'", $name, $validator)
                );
            }
        }

        return vsprintf($this->reversedTemplate, $this->sortArrayByArray($parameters, $this->parameters));
    }

    /**
     * @param string $path
     * @param array $parameters
     * @return bool
     */
    public function match($path, array & $parameters)
    {
        $result = (bool) preg_match($this->matchRegex, $path);

        if(true === $result) {
            preg_match_all($this->matchRegex, $path, $matches);

            foreach($this->parameters as $parameter) {
                if(isset($this->validators[$parameter])
                    && !preg_match($this->validators[$parameter], $matches[$parameter][0])) {
                    $parameters = [];
                    return false;
                }

                $parameters[$parameter] = $matches[$parameter][0];
            }
        }

        return $result;
    }

    /**
     * @return void
     */
    protected function parse()
    {
        if(preg_match_all(self::PARAMETER_REGEX, $this->expression, $matches) && count($matches) === 3) {
            $this->parameters = $matches[2];
        }

        $this->matchRegex = sprintf(
            self::REGEX_TPL,
            trim(preg_replace(self::PARAMETER_REGEX, self::MATCH_PARAMETER_REPLACEMENT, $this->expression))
        );

        $this->reversedTemplate = preg_replace(
            self::PARAMETER_REGEX,
            self::REVERSE_PARAMETER_REPLACEMENT,
            $this->expression
        );
    }

    /**
     * @param array $array
     * @param array $orderArray
     * @return array
     */
    protected function sortArrayByArray(array $array, array $orderArray) {
        $ordered = array();
        foreach($orderArray as $key) {
            if(array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['expression', 'parameters', 'matchRegex', 'reversedTemplate', 'validators'];
    }
} 