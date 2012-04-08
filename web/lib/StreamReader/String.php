<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.
 * @copyright (c) 2009, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPLv3 (or at your option, any later version).
 *
 * @package StreamReader
 */

/**
 * String reader.
 *
 * Reads buffer as stream.
 */
class StreamReader_String extends StreamReader_Abstract
{
    /**
     * Position.
     *
     * @var integer
     */
    private $_pos;

    /**
     * Stream.
     *
     * @var string
     */
    private $_stream;

    /**
     * Constructor.
     *
     * @param string $str The string to read (default: empty string).
     */
    public function __construct($str = '')
    {
        $this->_stream = $str;
        $this->_pos = 0;
    }

    /**
     * Read from string.
     *
     * @param integer $bytes Bytes to read.
     *
     * @return string The portion of the string read.
     */
    public function read($bytes)
    {
        $data = substr($this->_stream, $this->_pos, $bytes);
        $this->_pos += $bytes;
        if (strlen($this->_stream) < $this->_pos) {
            $this->_pos = strlen($this->_stream);
        }

        return $data;
    }

    /**
     * Seek to position.
     *
     * @param integer $pos Position.
     *
     * @return integer The seek-to position.
     */
    public function seekto($pos)
    {
        $this->_pos = $pos;
        if (strlen($this->_stream) < $this->_pos) {
            $this->_pos = strlen($this->_stream);
        }
        return $this->_pos;
    }

    /**
     * Get current position.
     *
     * @return integer The current position.
     */
    public function currentpos()
    {
        return $this->_pos;
    }

    /**
     * Get length.
     *
     * @return integer The length.
     */
    public function length()
    {
        return strlen($this->_stream);
    }

    /**
     * Set stream.
     *
     * @param string $stream The stream.
     *
     * @return void
     */
    public function setStream($stream)
    {
        $this->_stream = $stream;
    }
}
