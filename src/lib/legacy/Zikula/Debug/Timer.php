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
 * Simple timer class to measure code execution times.
 *
 * You can take multiple snapshots by calling the snap() function.
 * For multiple measurements with 1 Timer, some basic statistics
 * are computed.
 * @deprecated
 */
class Zikula_Debug_Timer
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
     * @param string $name The name of the timer
     */
    public function __construct($name = '')
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        $this->name = $name;
        $this->times = [];
        $this->start();
    }

    /**
     * Reset the timer.
     *
     * @param string $name The name of the timer
     *
     * @return void
     */
    public function reset($name = '')
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        $this->name = $name;
        $this->times = [];
        $this->start();
    }

    /**
     * Return the current microtime.
     *
     * @return float The current microtime
     */
    public function get_microtime()
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        return microtime(true);
    }

    /**
     * Start the timer.
     *
     * @return void
     */
    public function start()
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        $this->times[] = $this->get_microtime();
    }

    /**
     * Stop the timer.
     *
     * @param boolean $insertNewRecord Whether to insert a new record
     *
     * @return array Statistics
     */
    public function stop($insertNewRecord = true)
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        if ($insertNewRecord) {
            $this->times[] = $this->get_microtime();
        }

        if (count($this->times) <= 2) {
            return $this->stop_single();
        }

        return $this->stop_multiple();
    }

    /**
     * Print the timer results for a single measurement,
     *
     * @return array
     */
    public function stop_single()
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

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
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        $min = 9999999;
        $max = -9999999;
        $sum = 0;
        $size = count($this->times);
        $start = $this->times[0];
        $d = 0;
        $data = [];

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
     * @param boolean $doStats Whether to return statistics or not
     *
     * @return array|void
     */
    public function snap($doStats = false)
    {
        @trigger_error('Debug timer is deprecated, please use Symfony StopWatch instead.', E_USER_DEPRECATED);

        $this->times[] = $this->get_microtime();
        if ($doStats) {
            return $this->stop_multiple();
        }
    }
}
