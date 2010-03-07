<?php
/**
 * Zikula Application Framework
 *
 * @Copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.
 * @copyright (c) 2009, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL version 2 (or at your option, any later version).
 */

/**
 * File reader with seek capability
 * reads file as required
 *
 */
class FileReader extends StreamReader
{
    private $_pos;
    private $_fd;
    private $_length;

    public function __construct($filename)
    {
        if (file_exists($filename)) {

            $this->_length = filesize($filename);
            $this->_pos = 0;
            $this->_fd = fopen($filename, 'rb');
            if (!$this->_fd) {
                $this->setError(3); // Cannot read file, probably permissions
            }
        } else {
            $this->setError(0); // File doesn't exist
        }
    }

    public function read($bytes)
    {
        if ($bytes) {
            fseek($this->_fd, $this->_pos);

            // PHP 5.1.1 does not read more than 8192 bytes in one fread()
            // the discussions at PHP Bugs suggest it's the intended behaviour
            $data = '';
            while ($bytes > 0) {
                $chunk = fread($this->_fd, $bytes);
                $data .= $chunk;
                $bytes -= strlen($chunk);
            }
            $this->_pos = ftell($this->_fd);

            return $data;
        } else
            return '';
    }

    public function seekto($pos)
    {
        fseek($this->_fd, $pos);
        $this->_pos = ftell($this->_fd);
        return $this->_pos;
    }

    public function currentpos()
    {
        return $this->_pos;
    }

    public function length()
    {
        return $this->_length;
    }

    public function close()
    {
        fclose($this->_fd);
    }

}
