<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:33 PM
 */

namespace OpenFW\Configuration;


use OpenFW\Constants;
use OpenFW\Filesystem\RegexWalker;

class Configurator
{
    const DEFAULT_IT_REGEX = ".*\\.(php|ini)";

    /**
     * @var array
     */
    protected $parsers = [
        'php' => 'native',
        'ini' => 'ini'
    ];

    /**
     * @var \Iterator
     */
    protected $it;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var array|null
     */
    protected $config;

    /**
     * @param string $environment
     * @param \Iterator $it
     */
    public function __construct($environment, \Iterator $it)
    {
        $this->environment = $environment;
        $this->it = $it;
    }

    /**
     * @param string $directory
     * @return Configurator
     */
    public function createNew($directory)
    {
        return new self($this->environment, (new RegexWalker(
            $directory, self::DEFAULT_IT_REGEX
        ))->iterator(RegexWalker::IT_FILES | RegexWalker::IT_LINKS));
    }

    /**
     * @throws \RuntimeException
     */
    public function parse()
    {
        $config = [];

        /** @var $file \DirectoryIterator */
        foreach($this->it as $file) {
            foreach($this->parsers as $parserSuffix => $parserName) {
                if(preg_match(sprintf("#%s$#ui", $parserSuffix), $file->getFilename())) {
                    $parserClass = $this->buildParserClassName($parserName);

                    $config = array_merge_recursive($config, (new $parserClass($file->getRealPath()))->parseConfig());
                    break;
                }
            }
        }

        // check if environment exists
        if(!isset($config[$this->environment])) {
            throw new \RuntimeException("Unable to find configuration for {$this->environment} environment");
        }

        $this->config = $config[$this->environment];

        (new Validator($this->config))->validate();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $parserName
     * @return string
     */
    protected function buildParserClassName($parserName)
    {
        return sprintf("%s\\Parsers\\%sParser", __NAMESPACE__, ucfirst($parserName));
    }
} 