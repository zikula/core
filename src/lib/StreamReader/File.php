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
 * File reader with seek capability.
 *
 * Reads file as required.
 */
class StreamReader_File extends StreamReader_Abstract
{
    /**
     * Position.
     *
     * @var integer
     */
    private $_pos;

    /**
     * File handler.
     *
     * @var Filehandler
     */
    private $_fd;

    /**
     * Length.
     *
     * @var integer
     */
    private $_length;

    /**
     * Constructor.
     *
     * @param string $filename Filename
     */
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

    /**
     * Read file.
     *
     * @param integer $bytes Num of bytes to read
     *
     * @return string
     */
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
        } else {
            return '';
        }
    }

    /**
     * Seek to position.
     *
     * @param integer $pos Position
     *
     * @return integer Position
     */
    public function seekto($pos)
    {
        fseek($this->_fd, $pos);
        $this->_pos = ftell($this->_fd);

        return $this->_pos;
    }

    /**
     * Get current position.
     *
     * @return integer
     */
    public function currentpos()
    {
        return $this->_pos;
    }

    /**
     * Get length.
     *
     * @return integer
     */
    public function length()
    {
        return $this->_length;
    }

    /**
     * Close file.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->_fd);
    }
}
