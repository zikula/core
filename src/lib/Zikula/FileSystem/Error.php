<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_FileSystem_Error class deals with errors which may be thrown by drivers.
 *
 * Zikula_FileSystem_Driver class extends this class.
 */
class Zikula_FileSystem_Error
{
    /**
     * Error level.
     *
     * @var integer
     */
    //private $error_level;


    /**
     * Error container.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Get the last error to occur and return it.
     *
     * @param boolean $clear True the last error will be removed (optional) (default = false).
     *
     * @return An array of the last error or false on no errors
     */
    public function getLast($clear = false)
    {
        if (count($this->errors) < 1) {
            return false;
        }

        if ($clear) {
            $error = array_shift($this->errors);

            return $error;
        }

        return $this->errors[0];
    }

    /**
     * Count all errors which have occured, this is reset if the errors are cleared.
     *
     * @return integer Number of errors which exist.
     */
    public function count()
    {
        return count($this->errors);
    }

    /**
     * Get all of the errors that have occured.
     *
     * If the errors have been cleared then this will only get errors which
     * have occured since them.
     *
     * @param boolean $clear If true the last error will be removed (optional) (default = false).
     *
     * @return array Array of arrays which contain the errors.
     */
    public function getAll($clear = false)
    {
        if ($clear) {
            $errors = $this->errors;
            $this->clearAll();

            return $errors;
        }

        return $this->errors;
    }

    /**
     * Clear all of the registered errors.
     *
     * @return void
     */
    public function clearAll()
    {
        $this->errors = array();
    }

    /**
     * Start error handler.
     *
     * @return void
     */
    public function start()
    {
        // $this->error_level = error_reporting();
        // error_reporting(EALL | EWARNING);
        // return;
        //@codeCoverageIgnoreStart
        set_error_handler(array(
            $this,
            'handler'));
        //@codeCoverageIgnoreEnd
    }

    /**
     * Stop error handler.
     *
     * @return void
     */
    public function stop()
    {
        //@codeCoverageIgnoreStart
        //   error_reporting($this->error_level);
        restore_error_handler();
        //@codeCoverageIgnoreEnd
    }

    /**
     * Register an error. This helps to keep track of internal errors.
     *
     * The use of the error code allows the user to figure out why something went wrong.
     * The errors are registered in reverse order such that errors['0'] is most recent.
     * TODO determine a list of error codes for every possible failure.
     * TODO registered errors should have the method, file, message, code in the error so you know exactly what failed.
     *
     * @param string  $e    The error message to store.
     * @param integer $code The error code.
     *
     * @return void
     */
    public function register($e, $code)
    {
        array_unshift($this->errors,
            array(
                'message' => $e,
                'code' => $code
                )
            );
    }

    /**
     * Error handler.
     *
     * @param integer $errno   The error number.
     * @param string  $errstr  The error message.
     * @param string  $errfile The file where the error occurred.
     * @param integer $errline The line number where the error occurred.
     *
     * @return void
     */
    public function handler($errno, $errstr, $errfile, $errline)
    {
        $this->register($errstr, 0);
    }
}
