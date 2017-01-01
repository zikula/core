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
 * StreamReader Base.
 * @deprecated remove at Core-2.0
 */
abstract class StreamReader_Abstract
{
    /**
     * Error.
     *
     * @var string
     */
    private $error;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Read.
     *
     * @param string $bytes Bytes
     *
     * @return string Of bytes
     */
    abstract public function read($bytes);

    /**
     * Seek to.
     *
     * Should return new position
     *
     * @param integer $position Position
     *
     * @return integer Position
     */
    abstract public function seekto($position);

    /**
     * Return the current position.
     *
     * @return integer The current position
     */
    abstract public function currentpos();

    /**
     * Length.
     *
     * Returns length of entire stream (limit for seekto()s).
     *
     * @return integer The length
     */
    abstract public function length();

    /**
     * Set error property.
     *
     * @param string $error The error
     *
     * @return void
     */
    protected function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Get error.
     *
     * @return string The error
     */
    public function getError()
    {
        return $this->error;
    }
}
