<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/11/13
 * @time 3:47 PM
 */

namespace OpenFW\Configuration;


use OpenFW\Constants;
use OpenFW\Filesystem\RegexWalker;

class BundleConfigurator extends Configurator
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @param string $environment
     * @param string $directory
     */
    public function __construct($environment, $directory)
    {
        $this->directory = $directory;

        parent::__construct($environment, (new RegexWalker(
                                            $directory, self::DEFAULT_IT_REGEX
                                        ))->iterator(RegexWalker::IT_FILES | RegexWalker::IT_LINKS));
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
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
            throw new \RuntimeException(
                sprintf("Unable to find configuration for %s environment (in %s)", $this->environment, $this->directory)
            );
        }

        $this->config = $config[$this->environment];
    }
} 