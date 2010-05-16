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

/**
 * Smarty function to get the site's page render time
 *
 * available parameters:
 *  - assign      if set, the messages will be assigned to this variable
 *  - round       if the, the time will be rounded to this number of decimal places
 *                (optional: default 2)
 *
 * Example
 * <!--[sqldebug]-->
 *
 * @param    array    $params     All attributes passed to this function from the template
 * @param    object   $smarty     Reference to the Smarty object
 * @param    string   $round      format to apply to the number (based on the round php function)
 * @return   string   the page render time in seconds
 * @todo change to ML as language is hardcoded (as per .726).
 */
function smarty_function_sqldebug ($params, &$smarty)
{
    // show time to render
    $messages = array();

    global $ZConfig;
    global $ZRuntime;

    // determine log output generation
    $logDest = isset($ZConfig['Log']['log_dest']) ? $ZConfig['Log']['log_dest'] : '';
    if (isset($ZConfig['Log']['log_level_dest']['DB']) && $ZConfig['Log']['log_level_dest']['DB']) {
        $logDest = $ZConfig['Log']['log_level_dest']['DB'];
    }
    // generate count message
    $countRequest = 1;
    if ($ZConfig['Debug']['sql_count']) {
        $count      = (int)$ZRuntime['sql_count_request'];
        if ($logDest == 'PRINT') {
            $messages[] = '<div class="z-sub" style="text-align:center;">' . "Count: $count SQL statements" . '</div>';
        }
        else {
            $messages[] = "Count: $count SQL statements";
        }
    }

    // generate sql trace messages
    if ($ZConfig['Debug']['sql_time'] || $ZConfig['Debug']['sql_detail']) {
        $time  = $ZRuntime['sql_time_request'];
        $count = $ZRuntime['sql_count_request'];
        $avg   = $time / ($count ? $count : 1);

        $round = isset($params['round']) ? $params['round'] : 3;
        $time  = round($time, $round);
        $avg   = round($avg, $round);

        if ($logDest == 'PRINT') {
            $messages[] = '<div class="z-sub" style="text-align:center;">' . "SQL Exec Time: $time (total), $avg (avg) seconds" . '</div>';
            $line = '<div class="z-sub" style="text-align:left;">';
        } else {
            $messages[] = "SQL Exec Time: $time (total), $avg (avg) seconds.";
            $line = '';
        }

        $br = ($logDest == 'PRINT' ? '<br />' : '');
        $c  = 1;

        foreach ($ZRuntime['sql'] as $sql)
        {
            $clean = str_replace ("\n", '', $sql['stmt']);
            $clean = str_replace ('  ', ' ', $clean);
            $line .= "SQL Stmt #$c $br\n";
            $line .= "- $clean $br\n";

            if (isset($sql['limit'])) {
              $line .= "-- Limit: $sql[limit]$br\n";
            }

            $line .= "-- Rows Affected: $sql[rows_affected] $br\n";
            if (isset($sql['rows_marshalled'])) {
                $line .= "-- Rows Marshalled: $sql[rows_marshalled] $br\n";
            }

            $line .= "-- Time: $sql[time] $br\n";

            if (isset($sql['rows'])) {
                $ct = 1;
                foreach($sql['rows'] as $row) {
                    $line .= "--- Row $ct: " . implode ('|', $row) . "$br\n";
                    $ct++;
                }
            }

            $line .= "$br\n";
            $c++;
        }

        if ($logDest == 'PRINT') {
            $line .= '</div>';
        }

        $messages[] = $line;
    }

    $output = implode ("\n", $messages);

    if ($logDest == 'PRINT') {
        return $output;
    }

    LogUtil::log ($output, 'DB');

    return '';
}
