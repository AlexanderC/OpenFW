<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:33 PM
 */

namespace OpenFW\Configuration;


use OpenFW\Constants;
use OpenFW\Filesystem\RegexWalker;

class RawConfigurator
{
    const DEFAULT_IT_REGEX = ".*\\.(php|ini|yml|yaml)";

    /**
     * @var array
     */
    protected $parsers = [
        'php' => 'native',
        'ini' => 'ini',
        'yml' => 'yaml',
        'yaml' => 'yaml',
    ];

    /**
     * @var \Iterator
     */
    protected $it;

    /**
     * @var array|null
     */
    protected $config;

    /**
     * @param \Iterator $it
     */
    public function __construct(\Iterator $it)
    {
        $this->it = $it;
    }

    /**
     * @param string $directory
     * @return Configurator
     */
    public static function create($directory)
    {
        return new self((new RegexWalker(
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

        $this->config = $config;
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
        return sprintf("OpenFW\\Configuration\\Parsers\\%sParser", ucfirst($parserName));
    }
} 