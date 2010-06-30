<?php
/**
 * Zikula Application Framework.
 *
 * @copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.
 * @copyright (c) 2009, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPLv3 (or at your option, any later version).
 *
 * @package StreamReader
 */

/**
 * File reader with seek ability.
 *
 * Reads whole file at once.
 */
class StreamReader_CachedFile extends StreamReader_String
{
    /**
     * Constructor.
     *
     * @param string $filename Filename.
     */
    public function __construct($filename)
    {
        if (is_readable($filename)) {
            $this->setStream(file_get_contents($filename));
        } else {
            $this->setError(2); // File doesn't exist
        }
    }
}
