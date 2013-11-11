<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 1:54 PM
 */

namespace OpenFW\Filesystem;


class RegexWalker
{
    const IT_FILES = 0x001;
    const IT_FOLDERS = 0x002;
    const IT_LINKS = 0x004;

    const REGEX_ANY = '.*';
    const DEFAULT_DELIMITER = '#';
    const DEFAULT_FLAGS = 'u';

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $rawRegex;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @var string
     */
    protected $flags;

    /**
     * @param string $root
     * @param string $regex
     * @param string $delimiter
     * @param string $flags
     * @throws \RuntimeException
     */
    public function __construct(
        $root, $regex = self::REGEX_ANY,
        $delimiter = self::DEFAULT_DELIMITER,
        $flags = self::DEFAULT_FLAGS
    ) {
        $this->root = $root;
        $this->rawRegex = $regex;
        $this->delimiter = $delimiter;
        $this->flags = $flags;
        $this->regex = $this->buildRegex();
    }

    /**
     * @return void
     */
    protected function buildRegex()
    {
        return "{$this->delimiter}^{$this->rawRegex}\${$this->delimiter}{$this->flags}";
    }

    /**
     * @param bool $flags
     * @return \Generator
     */
    public function iterator($flags = false)
    {
        $directoryIterator = new \DirectoryIterator($this->root);
        $regexIterator = new \RegexIterator($directoryIterator, $this->regex);

        /** @var $file \DirectoryIterator */
        foreach($regexIterator as $file) {
            if(!$file->isDot()
                && (
                    false === $flags
                    || (($flags & self::IT_FILES) && $file->isFile())
                    || (($flags & self::IT_FOLDERS) && $file->isDir())
                    || (($flags & self::IT_LINKS) && $file->isLink())
                )
            ) {
                yield $file;
            }
        }
    }
} 