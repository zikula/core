<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Debug
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Exit.
 *
 * @param string  $msg  Message.
 * @param boolean $html True for html.
 *
 * @global array $ZConfig Configuration.
 * @return void|boolean
 */
function z_exit($msg, $html = true)
{
    global $ZConfig;
    if (System::isInstalling() && $ZConfig['System']['development']) {
        print(__f('Installation error: %s', $msg) . '<br />');
        prayer(debug_backtrace());
        return false;
    }

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
 * Serialize the given data in an easily human-readable way for debug purposes.
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707.
 *
 * @param array   $data           The object to serialize.
 * @param boolean $functions      Whether to show function names for objects (default=false) (optional).
 * @param integer $recursionLevel The current recursion level.
 *
 * @return string A string containing serialized data.
 */
function _prayer($data, $functions = false, $recursionLevel = 0)
{
    if ($recursionLevel > 5) {
        return __('Maximum recursion level reached');
    }

    global $ZConfig;
    if (System::isInstalling() && !$ZConfig['System']['development']) {
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
                            $value = (int)$value;
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


/**
 * A prayer shortcut.
 *
 * @param array   $data The object to serialize.
 * @param boolean $die  Whether to shutdown the process or not.
 *
 * @return void
 */
function z_prayer($data, $die = true)
{
    echo _prayer($data);

    if ($die) {
        System::shutdown();
    }
}

/**
 * Serialize the given data in an easily human-readable way for debug purposes.
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707.
 *
 * @param array   $data      The object to serialize.
 * @param boolean $functions Whether to show function names for objects (default=false) (optional).
 *
 * @return void
 */
function prayer($data, $functions = false)
{
    global $ZConfig;
    if (System::isInstalling() && !$ZConfig['System']['development']) {
        return;
    }

    $text = '<div style="text-align:left">';
    $text .= _prayer($data, $functions);
    $text .= '</div>';
    print($text);
}



/**
 * Simple timer class to measure code execution times.
 *
 * You can take multiple snapshots by calling the snap() function.
 * For multiple measurements with 1 Timer, some basic statistics
 * are computed.
 */
class Timer
{
    /**
     * The name of the timer.
     *
     * @var string
     */
    public $name;

    /**
     * Holds the times when to trigger the timer.
     *
     * @var array
     */
    public $times;

    /**
     * Constructor.
     *
     * @param string $name The name of the timer.
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->times = array();
        $this->start();
    }

    /**
     * Reset the timer.
     *
     * @param string $name The name of the timer.
     *
     * @return void
     */
    public function reset($name = '')
    {
        $this->name = $name;
        $this->times = array();
        $this->start();
    }

    /**
     * Return the current microtime.
     *
     * @return float The current microtime.
     */
    public function get_microtime()
    {
        return microtime(true);
    }

    /**
     * Start the timer.
     *
     * @return void
     */
    public function start()
    {
        $this->times[] = $this->get_microtime();
    }

    /**
     * Stop the timer.
     *
     * @param boolean $insertNewRecord Whether to insert a new record.
     *
     * @return array Statistics
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
     * Print the timer results for a single measurement,
     *
     * @return array
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
     * Print the timer results for multiple measurement.
     *
     * @return array
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
     * Take a snapshot while continuing the timing run.
     *
     * @param boolean $doStats Whether to return statistics or not.
     *
     * @return array|void
     */
    public function snap($doStats = false)
    {
        $this->times[] = $this->get_microtime();
        if ($doStats) {
            return $this->stop_multiple();
        }
    }
}
