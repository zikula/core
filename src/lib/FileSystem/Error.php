<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * FileSystem_Error class deals with errors which may be thrown by drivers.
 *
 * FileSystem_Driver class extends this class.
 */
class FileSystem_Error
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
    public function error_get_last($clear = false)
    {
        if (count($this->errors) < 1) {
            return false;
        }
        //TODO isnt there a php function get get an element from array and remove it?
        if ($clear) {
            $error = $this->errors[0];
            unset($this->errors[0]);
            $this->errors = array_values($this->errors);
            return $error;
        }
        return $this->errors[0];
    }

    /**
     * Count all errors which have occured, this is reset if the errors are cleared.
     *
     * @return 		Integer of the number of errors which exist.
     */
    public function error_count()
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
     * @return Array of arrays which contain the errors.
     */
    public function error_get_all($clear = false)
    {
        if ($clear) {
            $errors = $this->errors;
            $this->error_clear_all();
            return $errors;
        }
        return $this->errors;
    }

    /**
     * Clear all of the registered errors.
     *
     * @return void
     */
    public function error_clear_all()
    {
        unset($this->errors);
        $this->errors = array();
    }

    /**
     * Start error handler.
     *
     * @return void
     */
    public function start_handler()
    {
        // $this->error_level = error_reporting();
        // error_reporting(EALL | EWARNING);
        // return;
        set_error_handler(array(
            $this,
            'error_handler'));
    }

    /**
     * Stop error handler.
     *
     * @return void
     */
    public function stop_handler()
    {
        //   error_reporting($this->error_level);
        restore_error_handler();
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
    protected function error_register($e, $code)
    {
        $this->errors = array_merge(array(
            array(
                'message' => $e,
                'code' => $code)), $this->errors);
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
    public function error_handler($errno, $errstr, $errfile, $errline)
    {
        /*
        $errors = $this->error_codes();
        foreach ($errors as $key => $error) {
            if (stripos($errstr, $error['search']) !== FALSE) {
                $this->error_register($errstr, $error['code']);
                return true;
            }
         */
        $this->error_register($errstr, '0');
    }
}