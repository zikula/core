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
 * Zikula_Exception class.
 *
 * @deprecated
 */
class Zikula_Exception extends Exception
{
    /**
     * Debug value.
     *
     * @var mixed
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param string  $message Default ''
     * @param integer $code    Code
     * @param mixed   $debug   Debug
     */
    public function __construct($message = '', $code = 0, $debug = null)
    {
        @trigger_error('Zikula_Exception is deprecated.', E_USER_DEPRECATED);

        parent::__construct($message, $code);
        $this->debug = $debug;
    }

    /**
     * Get debug.
     *
     * @return array
     */
    public function getDebug()
    {
        return (array)$this->debug;
    }
}
