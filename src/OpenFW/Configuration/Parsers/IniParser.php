<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:27 PM
 */

namespace OpenFW\Configuration\Parsers;


class IniParser extends AbstractParser
{
    /**
     * @return array
     */
    public function parseConfig()
    {
        return parse_ini_file($this->file, true);
    }

} 