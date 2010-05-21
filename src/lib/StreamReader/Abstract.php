<?php
/**
 * Zikula Application Framework
 *
 * @Copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.
 * @copyright (c) 2009, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL version 2 (or at your option, any later version).
 */

 /**
 * StreamReader Base
 */
abstract class StreamReader_Abstract
{
    private $error;

    public function __construct() {}

    // should return a string [perhaps return array of bytes?]
    public function read($bytes)
    {
        return false;
    }

    // should return new position
    public function seekto($position)
    {
        return false;
    }

    // returns current position
    public function currentpos()
    {
        return false;
    }

    // returns length of entire stream (limit for seekto()s)
    public function length()
    {
        return false;
    }

    final protected function setError($error)
    {
        $this->error = $error;
    }

    final public function getError()
    {
        return $this->error;
    }
}
