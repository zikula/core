<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * constants for this class
 */
define('DATEFORMAT_FIXED', '%Y-%m-%d %H:%M:%S');
define('DATEONLYFORMAT_FIXED', '%Y-%m-%d');

/**
 * DateUtil.
 */
class DateUtil
{
    /**
     * Return a formatted datetime for the given timestamp (or for now).
     *
     * @param string  $time            The timestamp (string) which we wish to format (default==now).
     * @param string  $format          The format to use when formatting the date (optional).
     * @param boolean $translateFormat Whether the formatting should be translated or not (default = true).
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function getDatetime($time = null, $format = null, $translateFormat = true)
    {
        if (is_null($format)) {
            $format = DATEFORMAT_FIXED;
        }

        switch (trim(strtolower($format))) {
            case 'datelong':
                //! datelong
                $format = $translateFormat ? __('%A, %B %d, %Y') : '%A, %B %d, %Y';
                break;
            case 'datebrief':
                //! datebrief
                $format = $translateFormat ? __('%b %d, %Y') : '%b %d, %Y';
                break;
            case 'datestring':
                //! datestring
                $format = $translateFormat ? __('%A, %B %d @ %H:%M:%S') : '%A, %B %d @ %H:%M:%S';
                break;
            case 'datestring2':
                //! datestring2
                $format = $translateFormat ? __('%A, %B %d') : '%A, %B %d';
                break;
            case 'datetimebrief':
                //! datetimebrief
                $format = $translateFormat ? __('%b %d, %Y - %I:%M %p') : '%b %d, %Y - %I:%M %p';
                break;
            case 'datetimelong':
                //! datetimelong
                $format = $translateFormat ? __('%A, %B %d, %Y - %I:%M %p') : '%A, %B %d, %Y - %I:%M %p';
                break;
            case 'datefeed':
                //! RFC 822 date format for RSS feeds
                $format = $translateFormat ? __('%a, %d %b %Y %H:%M:%S %Z') : '%a, %d %b %Y %H:%M:%S %Z';
                break;
            case 'timebrief':
                //! timebrief
                $format = $translateFormat ? __('%I:%M %p') : '%I:%M %p';
                break;
            case 'timelong':
                //! timelong
                $format = $translateFormat ? __('%T %p') : '%T %p';
                break;
            default:
                $format = $translateFormat ? __($format) : $format;
                break;
        } // switch

        if ($time) {
            $dtstr = self::strftime($format, $time);
        } else {
            $dtstr = self::strftime($format);
        }

        return $dtstr;
    }

    /**
     * Transform a timestamp to internal datetime format.
     *
     * @param int $timestamp The timestamp.
     *
     * @return string The datetime into internal format.
     */
    public static function transformInternalDateTime($timestamp)
    {
        return self::strftime(DATEFORMAT_FIXED, $timestamp);
    }

    /**
     * Transform a timestamp to internal date only format.
     *
     * @param int $timestamp The timestamp.
     *
     * @return string The date into internal format.
     */
    public static function transformInternalDate($timestamp)
    {
        return self::strftime(DATEONLYFORMAT_FIXED, $timestamp);
    }

    /**
     * Reformat a given datetime according to the specified format.
     *
     * @param string  $datetime The (string) datetime to reformat.
     * @param string  $format   The format to use when formatting the date (optional).
     * @param boolean $TZadjust Adjust the output according to Timezone, default null.
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function formatDatetime($datetime = null, $format = DATEFORMAT_FIXED, $TZadjust = null)
    {
        if ($datetime === null) {
            return '';
        }

        if (!empty($datetime)) {
            if ($datetime instanceof DateTime) {
                $time = $datetime->getTimestamp();
            } else {
                $time = self::makeTimestamp($datetime);
            }
            //$time = self::parseUIDate($datetime);
        } else {
            $time = time();
        }

        // adjust with the user timezone diff
        if (is_null($TZadjust)) {
            $TZadjust = System::getVar('tzadjust', true);
        }
        if ($TZadjust) {
            $time -= self::getTimezoneUserDiff();
        }

        return self::getDatetime($time, $format);
    }

    /**
     * Build a datetime string from the supplied fields.
     *
     * This method uses the PHP function {@link http://www.php.net/mktime mktime}
     * to construct a UNIX timestamp prior to returning the output from
     * {@link DateUtil::strftime}. It is, therefore, subject to the same
     * limitations as mktime. Specifically, on most systems where time is stored
     * as a 32-bit integer the possible timestamps that can be constructed fall
     * between 13 Decemnber 1901 at 20:45:52 UTC and 19 January 2038 at
     * 03:14:07 UTC.
     *
     * @param integer $year   The year.
     * @param integer $month  The month.
     * @param integer $day    The day.
     * @param integer $hour   The hour (optional) (default==0).
     * @param integer $minute The minute (optional) (default==0).
     * @param integer $second The second (optional) (default==0).
     * @param string  $format The format to use when formatting the date (optional) (default==DATEFORMAT_FIXED).
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function buildDatetime($year, $month, $day, $hour = 0, $minute = 0, $second = 0, $format = DATEFORMAT_FIXED)
    {
        $dTime = mktime($hour, $minute, $second, $month, $day, $year);

        return self::strftime($format, $dTime);
    }

    /**
     * Return a formatted datetime at the end of the business day n days from now.
     *
     * @param integer $num    The number of days to advance (optional) (default=1).
     * @param string  $format The format to use when formatting the date (optional).
     * @param integer $year   The year of the target date to set (optional) (default=null, means params is taken from now).
     * @param integer $month  The month of the target date to set (optional) (default=null, means params is taken from now).
     * @param integer $day    The day of the target date to set (optional) (default=null, means params is taken from now).
     * @param integer $hour   The hour of the target time to set (optional) (default=null, means params is taken from now).
     * @param integer $minute The minute of the target time to set (optional) (default=null, means params is taken from now).
     * @param integer $second The second of the target time to set (optional) (default=null, means params is taken from now).
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function getDatetime_NextDay($num = 1, $format = DATEFORMAT_FIXED, $year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null)
    {
        $next = mktime($hour != null ? (int)$hour : date('H'),
                        $minute != null ? (int)$minute : date('i'),
                        $second != null ? (int)$second : date('s'),
                        $month != null ? (int)$month : date('m'),
                        $day != null ? (int)$day + $num : date('d') + $num,
                        $year != null ? (int)$year : date('y'));

        return self::strftime($format, $next);
    }

    /**
     * Return a formatted datetime at the end of the business day n week from now.
     *
     * @param integer $num    The number of weeks to advance (optional) (default=1).
     * @param string  $format The format to use when formatting the date (optional).
     * @param integer $year   The year of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $month  The month of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $day    The day of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $hour   The hour of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $minute The minute of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $second The second of the target time to set (optional) (default=null, means param is taken from now).
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function getDatetime_NextWeek($num = 1, $format = DATEFORMAT_FIXED, $year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null)
    {
        $num *= 7;
        $next = mktime($hour != null ? (int)$hour : date('H'),
                        $minute != null ? (int)$minute : date('i'),
                        $second != null ? (int)$second : date('s'),
                        $month != null ? (int)$month : date('m'),
                        $day != null ? (int)$day + $num : date('d') + $num,
                        $year != null ? (int)$year : date('y'));

        return self::strftime($format, $next);
    }

    /**
     * Return a formatted datetime at the end of the business day n months from now.
     *
     * @param integer $num    The number of months to advance (optional) (default=1).
     * @param string  $format The format to use when formatting the date (optional).
     * @param integer $year   The year of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $month  The month of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $day    The day of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $hour   The hour of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $minute The minute of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $second The second of the target time to set (optional) (default=null, means param is taken from now).
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function getDatetime_NextMonth($num = 1, $format = DATEFORMAT_FIXED, $year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null)
    {
        $next = mktime($hour != null ? (int)$hour : date('H'),
                        $minute != null ? (int)$minute : date('i'),
                        $second != null ? (int)$second : date('s'),
                        $month != null ? (int)$month + $num : date('m') + $num,
                        $day != null ? (int)$day : date('d'),
                        $year != null ? (int)$year : date('y'));

        return self::strftime($format, $next);
    }

    /**
     * Return a formatted datetime at the end of the business day n years from now
     *
     * @param integer $num    The number of years to advance (optional) (default=1).
     * @param string  $format The format to use when formatting the date (optional).
     * @param integer $year   The year of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $month  The month of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $day    The day of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $hour   The hour of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $minute The minute of the target time to set (optional) (default=null, means param is taken from now).
     * @param integer $second The second of the target time to set (optional) (default=null, means param is taken from now).
     *
     * @return string The datetime formatted according to the specified format.
     */
    public static function getDatetime_NextYear($num = 1, $format = DATEFORMAT_FIXED, $year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null)
    {
        $next = mktime($hour != null ? (int)$hour : date('H'),
                        $minute != null ? (int)$minute : date('i'),
                        $second != null ? (int)$second : date('s'),
                        $month != null ? (int)$month : date('m'),
                        $day != null ? (int)$day : date('d'),
                        $year != null ? (int)$year + $num : date('y') + $num);

        return self::strftime($format, $next);
    }

    /**
     * Return the date portion of a datetime timestamp.
     *
     * @param string $datetime The date to parse (optional) (default=='', reverts to now).
     * @param string $format   The format to use when formatting the date (optional).
     *
     * @return string The Date portion of the specified datetime.
     */
    public static function getDatetime_Date($datetime = '', $format = DATEFORMAT_FIXED)
    {
        if (!$datetime) {
            $datetime = self::getDatetime();
        }

        $dTime = strtotime($datetime);
        $sTime = self::getDatetime($dTime, $format);

        if ($format == DATEFORMAT_FIXED) {
            return substr($sTime, 0, 10);
        }

        $spaceOffset = strpos($datetime, ' ');

        return substr($sTime, 0, $spaceOffset);
    }

    /**
     * Return the time portion of a datetime timestamp.
     *
     * @param string $datetime The date to parse (optional) (default=='', reverts to now).
     * @param string $format   The format to use when formatting the date (optional).
     *
     * @return string The Time portion of the specified datetime.
     */
    public static function getDatetime_Time($datetime = '', $format = DATEFORMAT_FIXED)
    {
        if (!$datetime) {
            $datetime = self::getDatetime();
        }

        $dTime = strtotime($datetime);
        $sTime = self::getDatetime($dTime, $format);

        if ($format == DATEFORMAT_FIXED) {
            return substr($sTime, 11);
        }

        $spaceOffset = strpos($datetime, ' ');

        return substr($datetime, $spaceOffset + 1);
    }

    /**
     * Return the requested field from the supplied date.
     *
     * Since the date fields can change depending on the date format,
     * the following convention is used when referring to date fields:<br />
     *   Field 1    ->     Year<br />
     *   Field 2    ->     Month<br />
     *   Field 3    ->     Day<br />
     *   Field 4    ->     Hour<br />
     *   Field 5    ->     Minute<br />
     *   Field 6    ->     Second<br />
     *
     * @param string $datetime The field number to return.
     * @param string $field    The date to parse (default=='', reverts to now).
     *
     * @return string The requested datetime field
     */
    public static function getDatetime_Field($datetime, $field)
    {
        if (!$datetime) {
            $datetime = self::getDatetime();
        }

        $dTime = strtotime($datetime);
        $sTime = self::getDatetime($dTime);

        // adjust for human counting
        $field--;

        if ($field <= 2) {
            // looking for a date part
            $date = self::getDatetime_Date($sTime);
            $fields = explode('-', $date);
        } else {
            // looking for a time part
            $field -= 3;
            $time = self::getDatetime_Time($sTime);
            $fields = explode(':', $time);
        }

        return $fields[$field];
    }

    /**
     * Return an structured array holding the differences between 2 dates.
     *
     * The returned array will be structured as follows:<br>
     *   Array (<br />
     *          [d] => _numeric_day_value_<br />
     *          [h] => _numeric_hour_value_<br />
     *          [m] => _numeric_minute_value_<br />
     *          [s] => _numeric_second_value_ )<br />
     *
     * @param string $date1 The first date.
     * @param string $date2 The second date.
     *
     * @return array The structured array containing the datetime difference.
     */
    public static function getDatetimeDiff($date1, $date2)
    {
        if (!is_numeric($date1)) {
            $date1 = strtotime($date1);
        }

        if (!is_numeric($date2)) {
            $date2 = strtotime($date2);
        }

        $s = $date2 - $date1;
        $d = intval($s / 86400);
        $s -= $d * 86400;
        $h = intval($s / 3600);
        $s -= $h * 3600;
        $m = intval($s / 60);
        $s -= $m * 60;

        return array('d' => $d, 'h' => $h, 'm' => $m, 's' => $s);
    }

    /**
     * Return an field holding the differences between 2 dates expressed in units of the field requested.
     *
     * Since the date fields can change depending on the date format,
     * the following convention is used when referring to date fields:<br />
     *   Field 1    ->     Year<br />
     *   Field 2    ->     Month<br />
     *   Field 3    ->     Day<br />
     *   Field 4    ->     Hour<br />
     *   Field 5    ->     Minute<br />
     *   Field 6    ->     Second<br />
     *
     * @param string  $date1 The first date.
     * @param string  $date2 The second date.
     * @param integer $field The field (unit) in which we want the different (optional) (default=5).
     *
     * @return float The difference in units of the specified field.
     */
    public static function getDatetimeDiff_AsField($date1, $date2, $field = 5)
    {
        if (!is_numeric($date1)) {
            $date1 = strtotime($date1);
        }

        if (!is_numeric($date2)) {
            $date2 = strtotime($date2);
        }

        $s = $date2 - $date1;
        $diff = 0;

        if ($field == 1) {
            $diff = $s / (60 * 60 * 24 * 31 * 12);
        } elseif ($field == 2) {
            $diff = $s / (60 * 60 * 24 * 31);
        } elseif ($field == 3) {
            $diff = $s / (60 * 60 * 24);
        } elseif ($field == 4) {
            $diff = $s / (60 * 60);
        } elseif ($field == 5) {
            $diff = $s / (60);
        } else {
            $diff = $s;
        }

        return $diff;
    }

    /**
     * Calculate day-x of KW in a YEAR.
     *
     * @param integer $day  Values :0 for monday, 6 for sunday,....
     * @param integer $kw   Week of the year.
     * @param integer $year Year.
     * @param string  $flag The u or s (unixtimestamp or MySQLDate).
     *
     * @return unixtimestamp or sqlDate.
     */
    public static function getDateofKW($day, $kw, $year, $flag = 's')
    {
        $wday = date('w', mktime(0, 0, 0, 1, 1, $year)); // 1=Monday,...,7 = Sunday

        if ($wday <= 4) {
            $firstday = mktime(0, 0, 0, 1, 1 - ($wday - 1) + $day, $year);
        } else {
            $firstday = mktime(0, 0, 0, 1, 1 + (7 - $wday + 1) + $day, $year);
        }

        $month = date('m', $firstday);
        $year = date('Y', $firstday);
        $day = date('d', $firstday);

        $adddays = ($kw - 1) * 7;

        if ($flag != 's') {
            $return = mktime(0, 0, 0, $month, $day + $adddays, $year);
        } else {
            $return = self::getDatetime(mktime(0, 0, 0, $month, $day + $adddays, $year));
        }

        return $return;
    }

    /**
     * Return a the number of days in the given month/year.
     *
     * @param integer $month The (human) month number to check.
     * @param integer $year  The year number to check.
     *
     * @return integer The number of days in the given month/year
     */
    public static function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12) {
            return 0;
        }

        $days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        $d = $days[$month - 1];

        if ($month == 2) {
            // Check for leap year, no 4000 rule
            if ($year % 4 == 0) {
                if ($year % 100 == 0) {
                    if ($year % 400 == 0) {
                        $d = 29;
                    }
                } else {
                    $d = 29;
                }
            }
        }

        return $d;
    }

    /**
     * Return an array of weekdays for the given month.
     *
     * @param integer $month The (human) month number to check.
     * @param integer $year  The year number to check.
     *
     * @return integer The number of days in the given month/year.
     */
    public static function getWeekdaysInMonth($month, $year)
    {
        $nDays = self::getDaysInMonth($month, $year);

        $weekdays = array();
        for ($i = 1; $i <= $nDays; $i++) {
            $time = mktime(12, 0, 0, $month, $i, $year);
            $tDate = getdate($time);
            $weekdays[$i] = $tDate['wday'];
        }

        return $weekdays;
    }

    /**
     * Return an array of dates for the given month.
     *
     * @param integer $month The (human) month number to check.
     * @param integer $year  The year number to check.
     *
     * @return integer The number of days in the given month/year.
     */
    public static function getMonthDates($month, $year)
    {
        $dates = array();
        $days = self::getDaysInMonth($month, $year);

        for ($i = 1; $i <= $days; $i++) {
            $dates[$i] = self::buildDatetime($year, $month, $i);
        }

        return $dates;
    }

    /**
     * Parses a user interface date string (excluding time) into a timestamp.
     *
     * @param string $text   The UI date string.
     * @param string $format The format date string.
     *
     * @return string The timestamp or null in case of errors.
     */
    public static function parseUIDate($text, $format = null)
    {
        return self::parseUIDateTime($text, $format);
    }

    /**
     * Parses a user interface date+time string into a timestamp.
     *
     * @param string $text       The UI date+time string.
     * @param string $dateformat The format of the date.
     *
     * @return string The timestamp or null in case of errors.
     */
    public static function parseUIDateTime($text, $dateformat = null)
    {
        $format = self::getDateFormatData($dateformat);
        $yearPos = $format['matches']['year'];
        $monthPos = $format['matches']['month'];
        $dayPos = $format['matches']['day'];
        if ($format['type'] == 'datetimeshort') {
            $hourPos = $format['matches']['hour'];
            $minutePos = $format['matches']['minute'];
        } elseif ($format['type'] == 'datetimefull') {
            $hourPos = $format['matches']['hour'];
            $minutePos = $format['matches']['minute'];
            $secondPos = $format['matches']['second'];
        }

        $regex = $format['regex'];

        if (preg_match("/$regex/", $text, $matches)) {
            $year = $matches[$yearPos];
            $month = $matches[$monthPos];
            $day = $matches[$dayPos];
            $sec = 0;
            $min = 0;
            $hour = 0;

            if ($format['type'] == 'datetimeshort') {
                $hour = $matches[$hourPos];
                $min = $matches[$minutePos];
            } elseif ($format['type'] == 'datetimefull') {
                $hour = $matches[$hourPos];
                $min = $matches[$minutePos];
                $sec = $matches[$secondPos];
            }
            if (!checkdate($month, $day, $year) || $hour > 23 || $min > 59 || $sec > 59) {
                return null;
            }
        } else {
            return null;
        }

        return mktime($hour, $min, $sec, $month, $day, $year);
    }

    /**
     * Create a unix timestamp from either a unix timestamp (sic!), a MySQL timestamp or a string.
     *
     * This code is taken from smarty_make_timestamp.php, credits go to Monte Ohrt <monte at ohrt dot com>.
     *
     * We use a copy of the code here due to performance reasons.
     *
     * @param mixed $string A timestamp in one of the formats mentioned.
     *
     * @return integer A unix timestamp.
     */
    public static function makeTimestamp($string = '')
    {
        if (empty($string)) {
            // use 'now'
            $time = time();
        } elseif (preg_match('/^\d{14}$/', $string)) {
            // it is mysql timestamp format of YYYYMMDDHHMMSS?
            $time = mktime(substr($string, 8, 2), substr($string, 10, 2), substr($string, 12, 2),
                            substr($string, 4, 2), substr($string, 6, 2), substr($string, 0, 4));
        } elseif (is_numeric($string)) {
            // it is a numeric string, we handle it as timestamp
            $time = (int)$string;
        } else {
            // strtotime should handle it
            $time = strtotime($string);
            if ($time == -1 || $time === false) {
                // strtotime() was not able to parse $string, use 'now'
                $time = time();
            }
        }

        return $time;
    }

    /**
     * Identify timezone using the date PHP function.
     *
     * Does not use the strftime because it varies depending of the operative system.
     *
     * @return string timezone integer (hour value).
     */
    public static function getTimezone()
    {
        $ts = self::makeTimestamp();
        $tz = date('O', $ts);

        if (!is_numeric($tz)) {
            return false;
        }

        // we probably need some fixes depending on the day light saving here
        // fix the value to match the Zikula timezones ones
        return (float)sprintf('%2.2f', $tz / 100);
    }

    /**
     * Return the translated name of a specific timezone if exists.
     *
     * @param integer $tz Timezone identifier.
     *
     * @return string Timezone translation (hour value).
     */
    public static function getTimezoneText($tz = null)
    {
        if (!is_numeric($tz)) {
            return false;
        }

        $timezones = self::getTimezones();
        if (isset($timezones[$tz])) {
            return $timezones[$tz];
        }

        // string freeze: can't return 'Unknown timezone'
        return __('Unknown timezone');
    }

    /**
     * Return the translated list of timezones.
     *
     * @return array Timezones values and gettext strings.
     */
    public static function getTimezones()
    {
        return array('-12' => __('(GMT -12:00 hours) Baker Island'),
                '-11' => __('(GMT -11:00 hours) Midway Island, Samoa'),
                '-10' => __('(GMT -10:00 hours) Hawaii'),
                '-9.5' => __('(GMT -9:30 hours) French Polynesia'),
                '-9' => __('(GMT -9:00 hours) Alaska'),
                '-8' => __('(GMT -8:00 hours) Pacific Time (US & Canada)'),
                '-7' => __('(GMT -7:00 hours) Mountain Time (US & Canada)'),
                '-6' => __('(GMT -6:00 hours) Central Time (US & Canada), Mexico City'),
                '-5' => __('(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito'),
                '-4' => __('(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz'),
                '-3.5' => __('(GMT -3:30 hours) Newfoundland'),
                '-3' => __('(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown'),
                '-2' => __('(GMT -2:00 hours) Mid-Atlantic'),
                '-1' => __('(GMT -1:00 hours) Azores, Cape Verde Islands'),
                '0' => __('(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia'),
                '1' => __('(GMT +1:00 hours) CET (Central Europe Time), Brussels, Copenhagen, Madrid, Paris'),
                '2' => __('(GMT +2:00 hours) EET (Eastern Europe Time), Kaliningrad, South Africa'),
                '3' => __('(GMT +3:00 hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg'),
                '3.5' => __('(GMT +3:30 hours) Tehran'),
                '4' => __('(GMT +4:00 hours) Abu Dhabi, Muscat, Baku, Tbilisi'),
                '4.5' => __('(GMT +4:30 hours) Kabul'),
                '5' => __('(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent'),
                '5.5' => __('(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi'),
                '5.75' => __('(GMT +5:45 hours) Kathmandu'),
                '6' => __('(GMT +6:00 hours) Almaty, Dhaka, Colombo'),
                '6.5' => __('(GMT +6:30 hours) Cocos Islands, Myanmar'),
                '7' => __('(GMT +7:00 hours) Bangkok, Hanoi, Jakarta'),
                '8' => __('(GMT +8:00 hours) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei'),
                '9' => __('(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
                '9.5' => __('(GMT +9:30 hours) Adelaide, Darwin'),
                '10' => __('(GMT +10:00 hours) EAST (East Australian Standard)'),
                '10.5' => __('(GMT +10:30 hours) Lord Howe Island (NSW, Australia)'),
                '11' => __('(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia'),
                '11.5' => __('(GMT +11:30 hours) Norfolk Island'),
                '12' => __('(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island'),
                '12.75' => __('(GMT +12:45 hours) Chatham Islands'),
                '13' => __('(GMT +13:00 hours Tonga, Kiribati (Phoenix Islands)'),
                '14' => __('(GMT +14:00 hours) Kiribati (Line Islands)'));
    }

    /**
     * Identify timezone abbreviation using the date PHP function.
     *
     * Does not use the strftime because it varies depending of the operative system.
     *
     * @return string Timezone abbreviation.
     */
    public static function getTimezoneAbbr()
    {
        $ts = self::makeTimestamp();

        return date('T', $ts);
    }

    /**
     * Return the time difference between the server and user timezone in seconds.
     *
     * @return integer The time difference between the server and user timezone in seconds.
     */
    public static function getTimezoneUserDiff()
    {
        $srv_tz = System::getVar('timezone_server');
        $usr_tz = UserUtil::getVar('tzoffset') ? UserUtil::getVar('tzoffset') : System::getVar('timezone_offset');

        return ($srv_tz - $usr_tz) * 60 * 60;
    }

    /**
     * Multilingual format time method.
     *
     * @param string $format    Format date.
     * @param string $timestamp Timestamp.
     *
     * @return string The formatted time.
     */
    public static function strftime($format, $timestamp = null)
    {
        if (empty($format)) {
            return null;
        }

        if (empty($timestamp)) {
            $timestamp = time();
        }

        static $day_of_week_short, $month_short, $day_of_week_long, $month_long, $timezone;

        if (!isset($day_of_week_short)) {
            $day_of_week_short = explode(' ', __('Sun Mon Tue Wed Thu Fri Sat'));
            $month_short = explode(' ', __('Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec'));
            $day_of_week_long = explode(' ', __('Sunday Monday Tuesday Wednesday Thursday Friday Saturday'));
            $month_long = explode(' ', __('January February March April May June July August September October November December'));

            // build the timezone
            $timezone_all = explode(' ', __('GMT-12 GMT-11 HST GMT-9:30 AKST PST MST CST EST AST GMT-3:30 GMT-3 GMT-2 GMT-1 GMT CET EET GMT+3 GMT+3:30 GMT+4 GMT+4:30 GMT+5 GMT+5:30 GMT+5:45 GMT+6 GMT+6:30 GMT+7 AWST ACDT JST ACST AEST GMT+11 GMT+11:30 GMT+12 GMT+12:45 GMT+13 GMT+14'));
            $offset_all = explode(' ', __('-12 -11 -10 -9.5 -9 -8 -7 -6 -5 -4 -3.5 -3 -2 -1 0 1 2 3 3.5 4 4.5 5 5.5 5.75 6 6.5 7 8 9 9.5 10 10.5 11 11.5 12 12.75 13 14'));

            $thezone = null;
            if (UserUtil::isLoggedIn()) {
                $thezone = UserUtil::getVar('tzoffset');
            }
            $thezone = $thezone ? $thezone : System::getVar('timezone_offset');

            $timezone = 'GMT';
            $offset_all = array_flip($offset_all);
            if (isset($offset_all[$thezone])) {
                $timezone = $timezone_all[$offset_all[$thezone]];
            }
        }

        $trformat = preg_replace('/%a/', $day_of_week_short[strftime('%w', (int)$timestamp)], $format);
        $trformat = preg_replace('/%A/', $day_of_week_long[strftime('%w', (int)$timestamp)], $trformat);
        $trformat = preg_replace('/%b/', $month_short[strftime('%m', (int)$timestamp) - 1], $trformat);
        $trformat = preg_replace('/%B/', $month_long[strftime('%m', (int)$timestamp) - 1], $trformat);
        $trformat = preg_replace('/%Z/', $timezone, $trformat);

        return strftime($trformat, (int)$timestamp);
    }

    /**
     * Get dateformat data.
     *
     * Parses strftime formatted __('%Y-%m-%d),  __('%Y-%m-%d %H:%M') or __('%Y-%m-%d %H:%M:%S')
     * into meaning data that can be used to process a date string.
     *
     * format strings can contain %d, %e, %y, %Y, %g, %G, %H, %I, %l, %M and %S.
     *
     * @param string $dateformat Default current language default (strftime formatted).
     *
     * @return array Array of the meaning of each match.
     */
    public static function getDateFormatData($dateformat = null)
    {
        if (is_null($dateformat)) {
            $dateformat = __('%Y-%m-%d');
        }

        // 8 = __('%Y-%m-%d');
        // 14 = __('%Y-%m-%d %H:%M');
        // 17 = __('%Y-%m-%d %h:%M:%S');
        $length = strlen($dateformat);
        switch ($length) {
            case 8:
                $regex = '#%(\w)(.)%(\w)(.)%(\w)#';
                $type = 'date';
                break;
            case 14:
                $regex = '#%(\w)(.)%(\w)(.)%(\w)\s%(\w)(.)%(\w)#';
                $type = 'datetimeshort';
                break;
            case 17:
                $regex = '#%(\w)(.)%(\w)(.)%(\w)\s%(\w)(.)%(\w)(.)%(\w)#';
                $type = 'datetimefull';
                break;
            default:
                throw new \Exception(__f('Dateformat must be with 8, 14 or 17 characters long.', $dateformat));
        }

        if (preg_match($regex, $dateformat, $matches)) {
            $matchCount = count($matches);
            // validate separator
            if ($matches[2] != $matches[4]) {
                // TODO A throw exception here (dateformat separators must match) - drak
                throw new \Exception(__f('Dateformat separators must be the same in %s', $dateformat));
            }

            // construct separator regex
            $separator = preg_quote($matches[2]);

            $dateMap = array('d' => array('regex' => '(\d{2})', 'type' => 'day'),
                    'e' => array('regex' => '(\d{1,2})', 'type' => 'day'),
                    'm' => array('regex' => '(\d{2})', 'type' => 'month'),
                    'y' => array('regex' => '(\d{2})', 'type' => 'year'),
                    'Y' => array('regex' => '(\d{4})', 'type' => 'year'),
                    'g' => array('regex' => '(\d{2})', 'type' => 'year'),
                    'G' => array('regex' => '(\d{4})', 'type' => 'year'),
                    'H' => array('regex' => '(\d{2})', 'type' => 'hour'),
                    'I' => array('regex' => '(\d{2})', 'type' => 'hour'),
                    'l' => array('regex' => '(\d{1,2})', 'type' => 'hour'),
                    'M' => array('regex' => '(\d{2})', 'type' => 'minute'),
                    'S' => array('regex' => '(\d{2})', 'type' => 'second'));

            // define elements
            $format = array();
            $format[] = $matches[1]; // position 1
            $format[] = $matches[3]; // position 2
            $format[] = $matches[5]; // position 3
            if ($matchCount > 8) {
                if ($matchCount == 11 && $matches[7] != $matches[9]) {
                    // TODO A throw exception here (dateformat separators must match) - drak
                    throw new \Exception(__f('Dateformat time separators must be the same in %s', $dateformat));
                }

                $timeseparator = preg_quote($matches[7]);
                $format[] = $matches[6]; // position 3
                $format[] = $matches[8]; // position 3
                if ($matchCount == 11) {
                    $format[] = $matches[10]; // position 3
                }
            }

            // map elements
            foreach ($format as $key) {
                $meaning[] = array('key' => $key, 'type' => $dateMap[$key]['type'], 'regex' => $dateMap[$key]['regex']);
            }

            // build regex
            $regex = $meaning[0]['regex'] . $separator . $meaning[1]['regex'] . $separator . $meaning[2]['regex'];

            if ($matchCount > 7) {
                $regex .= '\s' . $meaning[3]['regex'] . $timeseparator . $meaning[4]['regex'];
                if ($matchCount == 11) {
                    $regex .= $timeseparator . $meaning[5]['regex'];
                }
            }

            // find month, day, year, hour, minute and second positions in the dateformat
            $count = 1;
            foreach ($meaning as $m) {
                $positionMatch[$m['type']] = $count;
                $count++;
            }

            // build and return array
            return array('regex' => $regex, 'matches' => $positionMatch, 'type' => $type);
        }

        // TODO A throw exception here in 1.3.0 - drak
        throw new \Exception(__f('Dateformat did not match known format in %s', $dateformat));
    }
}
