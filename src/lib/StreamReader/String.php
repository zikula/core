<?php
/**
 * Zikula Application Framework
 *
 * @Copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.
 * @copyright (c) 2009, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL version 2 (or at your option, any later version).
 *
 * @package StreamReader
 */

/**
 * String reader
 * reads buffer as stream
 *
 */
class StreamReader_String extends StreamReader_Abstract
{
    private $_pos;
    private $_stream;

    public function __construct($str = '')
    {
        $this->_stream = $str;
        $this->_pos = 0;
    }

    public function read($bytes)
    {
        $data = substr($this->_stream, $this->_pos, $bytes);
        $this->_pos += $bytes;
        if (strlen($this->_stream) < $this->_pos)
            $this->_pos = strlen($this->_stream);

        return $data;
    }

    public function seekto($pos)
    {
        $this->_pos = $pos;
        if (strlen($this->_stream) < $this->_pos)
            $this->_pos = strlen($this->_stream);
        return $this->_pos;
    }

    public function currentpos()
    {
        return $this->_pos;
    }

    public function length()
    {
        return strlen($this->_stream);
    }

    final public function setStream($stream)
    {
        $this->_stream = $stream;
    }

}
