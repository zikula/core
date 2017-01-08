<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File reader with seek ability.
 *
 * Reads whole file at once.
 * @deprecated remove at Core-2.0
 */
class StreamReader_CachedFile extends StreamReader_String
{
    /**
     * Constructor.
     *
     * @param string $filename Filename
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
