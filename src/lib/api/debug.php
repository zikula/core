<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

function z_exit($msg, $html = true)
{
    global $ZConfig;
    if (defined('_ZINSTALLVER') && $ZConfig['System']['development']) {
        print(__f('Installation error: %s', $msg) . '<br />');
        prayer(debug_backtrace());
        return false;
    }

    //Loader::loadClass('LogUtil');
    $msg = __('Exit handler:') . $msg;
    if ($ZConfig['System']['development']) {
        $msg .= "\n" . __('Stack trace:') . "\n";
    }
    $msg2 = str_replace("\n", '<br />', $msg);
    $debug = _prayer(debug_backtrace());

    LogUtil::log($msg . $debug, 'FATAL');

    global $ZConfig;
    if ($ZConfig['System']['development']) {
        print($msg2 . $debug);
        System::shutdown();
    }

    return LogUtil::registerError($msg2);
}

/**
 * Serialize the given data in an easily human-readable way for debug purposes
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707
 *
 * @param data        The object to serialize
 * @param functions   whether to show function names for objects (default=false) (optional)
 *
 * @return string A string containing serialized data
 */
if (!function_exists('_prayer')) {
    function _prayer($data, $functions = false, $recursionLevel = 0)
    {
        if ($recursionLevel > 5) {
            return __('Maximum recursion level reached');
        }

        global $ZConfig;
        if (defined('_ZINSTALLVER') && !$ZConfig['System']['development']) {
            return;
        }

        $text = '';

        if ($functions != 0) {
            $sf = 1;
        } else {
            $sf = 0;
        }

        if (isset($data)) {
            if (is_array($data) || is_object($data)) {
                $datatype = gettype($data);
                if (count($data)) {
                    $text .= "<ol>\n";

                    foreach ($data as $key => $value) {
                        $type = gettype($value);

                        if ($type == 'array' || ($type == 'object' && get_object_vars($value))) {
                            $text .= sprintf("<li>(%s) <strong>%s</strong>:\n", $type, $key);
                            $text .= _prayer($value, $sf, $recursionLevel + 1);
                            $text .= '</li>';

                        } elseif (preg_match('/function/i', $type)) {
                            if ($sf) {
                                $text .= sprintf("<li>(%s) <strong>%s</strong> </li>\n", $type, $key, $value);
                                // There doesn't seem to be anything traversable inside functions.
                            }
                        } else {
                            if (!isset($value)) {
                                $value = '(none)';
                            }

                            // You cannot do DataUtil::formatForDisplay on an object, so just display object type
                            if (is_object($value)) {
                                $value = gettype($value);

                            } elseif (is_bool($value)) {
                                $value = (int) $value;
                            }

                            // parse th eoutput
                            if ($datatype == 'array') {
                                $text .= sprintf("<li>(%s) <strong>%s</strong> = %s</li>\n", $type, $key, DataUtil::formatForDisplay($value));

                            } elseif ($datatype == 'object') {
                                $text .= sprintf("<li>(%s) <strong>%s</strong> -> %s</li>\n", $type, $key, DataUtil::formatForDisplay($value));
                            }
                        }
                    }

                    $text .= "</ol>\n";
                } else {
                    $text .= '(empty)';
                }
            } else {
                $text .= $data;
            }
        }
        return $text;
    }
}

/**
 * A prayer shortcut
 */
function z_prayer($data, $die = true)
{
    echo _prayer($data);

    if ($die) {
        System::shutdown();
    }
}

/**
 * Serialize the given data in an easily human-readable way for debug purposes
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707
 *
 * @param data        The object to serialize
 * @param functions   whether to show function names for objects (default=false) (optional)
 *
 * @return nothing, the data is directly printed
 */
if (!function_exists('prayer')) {
    function prayer($data, $functions = false)
    {
        global $ZConfig;
        if (defined('_ZINSTALLVER') && !$ZConfig['System']['development']) {
            return;
        }

        $text = '<div style="text-align:left">';
        $text .= _prayer($data, $functions);
        $text .= '</div>';
        print($text);
    }
}

/**
 * Simple timer class to measure code execution times.
 *
 * You can take multiple snapshots by calling the snap() function.
 * For multiple measurements with 1 Timer, some basic statistics
 * are computed.
 *
 * @package Zikula_Core
 * @subpackage Debug
 */
if (!class_exists('Timer')) {
    class Timer
    {
        public $name;
        public $times;

        /**
         * Constructor
         *
         * @param name    The name of the timer
         */
        public function __construct($name = '')
        {
            $this->name = $name;
            $this->times = array();
            $this->start();
        }

        /**
         * reset the timer
         *
         * @param name    The name of the timer
         */
        public function reset($name = '')
        {
            $this->name = $name;
            $this->times = array();
            $this->start();
        }

        /**
         * return the current microtime
         */
        public function get_microtime()
        {
            $tmp = explode(' ', microtime());
            $rt = $tmp[0] + $tmp[1];
            return $rt;
        }

        /**
         * start the timer
         */
        public function start()
        {
            $this->times[] = $this->get_microtime();
        }

        /**
         * stop the timer
         */
        public function stop($insertNewRecord = true)
        {
            if ($insertNewRecord)
                $this->times[] = $this->get_microtime();

            if (count($this->times) <= 2)
                return $this->stop_single();

            return $this->stop_multiple();
        }

        /**
         * print the timer results for a single measurement
         */
        public function stop_single()
        {
            $start = $this->times[0];
            $stop = $this->times[1];
            $diff = $stop - $start;

            $data['count'] = 1;
            $data['start'] = $start;
            $data['stop'] = $stop;
            $data['diff'] = $diff;

            return $data;
        }

        /**
         * print the timer results for multiple measurement
         */
        public function stop_multiple()
        {
            $min = 9999999;
            $max = -9999999;
            $sum = 0;
            $size = count($this->times);
            $start = $this->times[0];
            $d = 0;
            $data = array();

            for ($i = 1; $i < $size; $i++) {
                $last = $this->times[$i - 1];
                $stop = $this->times[$i];

                $diff = $stop - $last;

                if ($diff < $min) {
                    $min = $diff;
                }

                if ($diff > $max) {
                    $max = $diff;
                }

                $d += $diff;
            }

            $start = $this->times[0];
            $stop = $this->times[$size - 1];

            $avg = $d / $size;

            $data['count'] = $size;
            $data['start'] = $start;
            $data['stop'] = $stop;
            $data['min'] = $min;
            $data['max'] = $max;
            $data['avg'] = $avg;
            $data['diff'] = $d;
            //$stats = sprintf ("(%d runs, min=%f, max=%f, avg=%f)\n", $size, $min, $max, $avg);
            //print ("Timer: $diff $stats $this->name\n");
            return $data;
        }

        /**
         * take a snapshot while continuing the timing run
         */
        public function snap($doStats = false)
        {
            $this->times[] = $this->get_microtime();
            if ($doStats) {
                return $this->stop_multiple();
            }
        }
    }
}
