<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:25 PM
 */

namespace OpenFW\Configuration\Parsers;


class NativeParser extends AbstractParser
{
    /**
     * @return array
     */
    public function parseConfig()
    {
        return require $this->file;
    }

} 