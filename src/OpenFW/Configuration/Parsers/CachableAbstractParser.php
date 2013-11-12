<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/12/13
 * @time 9:51 AM
 */

namespace OpenFW\Configuration\Parsers;


use OpenFW\Constants;

abstract class CachableAbstractParser extends AbstractParser
{
    const DUMP_TPL = "<?php\n /** @time %s */\n\n return [\n%s\n];";

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function parseConfig()
    {
        $parsed = $this->tryGetCache();

        if(false === $parsed) {
            $parsed = $this->__parseInternal();

            if(!is_array($parsed)) {
                throw new \UnexpectedValueException("__parseInternal result should be an array.");
            }

            $this->persistCache($parsed);
        }

        return $parsed;
    }

    /**
     * Parse and than cache this...
     *
     * @return array
     */
    abstract protected function __parseInternal();

    /**
     * @return bool|mixed
     */
    protected function tryGetCache()
    {
        $file = $this->getCacheFileName();

        if(is_file($file)) {
            return require($file);
        }

        return false;
    }

    /**
     * @param array $data
     * @throws \RuntimeException
     */
    protected function persistCache(array $data)
    {
        $file = $this->getCacheFileName();

        $dir = dirname($file);

        if(!is_dir($dir) && !mkdir($dir, 0777, true)) {
            throw new \RuntimeException("Unable to create cache subdirectory.");
        }

        if(!file_put_contents(
            $file,
            sprintf(self::DUMP_TPL, date('d M Y H:i:s'), var_export($data, true)),
            LOCK_EX | LOCK_NB)
        ) {
            throw new \RuntimeException("Unable to persist config ini parser cache.");
        }
    }

    /**
     * @return string
     */
    protected function getCacheFileName()
    {
        static $filename;

        if(!isset($filename)) {
            $filename = sprintf(
                "%s/config/%s_%s/%s.php",
                Constants::getResolvedPath(Constants::CACHE_DIR),
                hash_file('sha256', $this->file),
                md5($this->file),
                basename($this->file)
            );
        }

        return $filename;
    }
} 