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
 * FilterUtil date handler plugin
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
class FilterUtil_Filter_Date extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface, FilterUtil_ReplaceInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $ops = [];

    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Constructor.
     *
     * Argument $config may contain
     *  fields:   Set of fields to use, see setFields().
     *  ops:      Operators to enable, see activateOperators().
     *
     * @param array $config Configuration.
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields']) && is_array($config['fields'])) {
            $this->addFields($config['fields']);
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators($this->availableOperators());
        }
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return ['eq', 'ne', 'gt', 'ge', 'lt', 'le'];
    }

    /**
     * Activates the requested Operators.
     *
     * @param mixed $op Operators to activate.
     *
     * @return void
     */
    public function activateOperators($op)
    {
        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $this->availableOperators()) !== false) {
            $this->ops[] = $op;
        }
    }

    /**
     * Adds fields to list in common way.
     *
     * @param mixed $fields Fields to add.
     *
     * @return void
     */
    public function addFields($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $fld) {
                $this->addFields($fld);
            }
        } elseif (!empty($fields) && $this->fieldExists($fields) && array_search($fields, $this->fields) === false) {
            $this->fields[] = $fields;
        }
    }

    /**
     * Returns the fields.
     *
     * @return array List of fields.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get operators
     *
     * @return array Set of Operators and Arrays
     */
    public function getOperators()
    {
        $fields = $this->getFields();
        if ($this->default == true) {
            $fields[] = '-';
        }

        $ops = [];
        foreach ($this->ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
    }

    /**
     * Replace field's value.
     *
     * @param string $field Field name.
     * @param string $op    Filter operator.
     * @param string $value Filter value.
     *
     * @return array New filter set.
     */
    public function replace($field, $op, $value)
    {
        // First check if this plugin have to work with this field
        if (false === array_search($field, $this->fields)) {
            return [$field, $op, $value]; // If not, return given value
        }

        // Now, work!
        // convert to unix timestamp
        if (false === ($date = $this->dateConvert($value))) {
            return false;
        }

        return [$field, $op, $date];
    }

    /**
     * Convert the date to a more useful format.
     *
     * @param string $date Date string.
     *
     * @return string Converted date
     */
    protected function dateConvert($date)
    {
        if (strptime($date, "%d.%m.%Y %H:%M:%S") !== false) {
            $arr = strptime($date, "%d.%m.%Y %H:%M:%S");
            $time = DateUtil::buildDatetime($arr['tm_year'], $arr['tm_mon'], $arr['tm_monday'], $arr['tm_hour'], $arr['tm_min'], $arr['tm_sec']);
        } elseif (is_numeric($date)) {
            $time = DateUtil::getDatetime($date);
        } else {
            $time = str_replace('_', ' ', $date);
        }

        return $time;
    }

    /**
     * Parses the date string to create a time period.
     *
     * Takes a date and calculates a period by a specific type.
     * Types may be:
     *   year:       That year.
     *   month:      That month.
     *   day:        That day.
     *   tomorrow:   The day after that day.
     *   hour:       That hour.
     *   min/minute: That minute.
     * Returns an array [from, to].
     *
     * @param string $date Date string.
     * @param string $type Period type.
     *
     * @return array Start and end of the period.
     */
    private function makePeriod($date, $type)
    {
        $datearray = getdate($date);

        switch ($type) {
            case 'year':
                $from = mktime(0, 0, 0, 1, 1, $datearray['year']);
                $to = strtotime('+1 year', $from);
                break;

            case 'month':
                $from = mktime(0, 0, 0, $datearray['mon'], 1, $datearray['year']);
                $to = strtotime('+1 month', $from);
                break;

            case 'week':
                $from = mktime(0, 0, 0, $datearray['mon'], $datearray['mday'], $datearray['year']);
                $from = ($datearray['wday'] != 1) ? strtotime('last monday', $from) : $from;
                $to = strtotime('+1 week', $from);
                break;

            case 'day':
            case 'tomorrow':
                $from = mktime(0, 0, 0, $datearray['mon'], $datearray['mday'], $datearray['year']);
                $to = strtotime('+1 day', $from);
                break;

            case 'hour':
                $from = mktime($datearray['hours'], 0, 0, $datearray['mon'], $datearray['mday'], $datearray['year']);
                $to = $from + 3600;
                break;

            case 'min':
            case 'minute':
                $from = mktime($datearray['hours'], $datearray['minutes'], 0, $datearray['mon'], $datearray['mday'], $datearray['year']);
                $to = $from + 60;
                break;
        }

        return [$from, $to];
    }

    /**
     * Returns SQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array SQL code array.
     */
    public function getSQL($field, $op, $value)
    {
        if (array_search($op, $this->ops) === false || array_search($field, $this->fields) === false) {
            return '';
        }

        $type = 'point';
        if (preg_match('~^(year|month|week|day|hour|min):\s*(.*)$~i', $value, $res)) {
            $type = strtolower($res[1]);
            if (strlen($res[2]) == 4) {
                $res[2] = "01.01." . $res[2];
            }
            $time = strtotime($res[2]);
        } elseif (preg_match('~(year|month|week|day|hour|min|tomorrow)~', $value, $res)) {
            $type = strtolower($res[1]);
            $time = strtotime($value);
        } else {
            $time = strtotime($value);
        }

        $column = $this->column[$field];

        switch ($op) {
            case 'eq':
                if ($type != 'point') {
                    list($from, $to) = $this->makePeriod($time, $type);
                    $where = "($column >= '".DateUtil::getDatetime($from)."' AND ".
                              "$column < '".DateUtil::getDatetime($to)."')";
                } else {
                    $where = "$column = '".DateUtil::getDatetime($time)."'";
                }
                break;

            case 'ne':
                if ($type != 'point') {
                    list($from, $to) = $this->makePeriod($time, $type);
                    $where = "($column < '".DateUtil::getDatetime($from)."' AND ".
                              "$column >= '".DateUtil::getDatetime($to)."')";
                } else {
                    $where = "$column <> '".DateUtil::getDatetime($time)."'";
                }
                break;

            case 'gt':
                if ($type != 'point') {
                    list($from, $time) = $this->makePeriod($time, $type);
                }
                $where = "$column > '".DateUtil::getDatetime($time)."'";
                break;

            case 'ge':
                $where = "$column >= '".DateUtil::getDatetime($time)."'";
                break;

            case 'lt':
                $where = "$column < '".DateUtil::getDatetime($time)."'";
                break;

            case 'le':
                if ($type != 'point') {
                    list($from, $time) = $this->makePeriod($time, $type);
                }
                $where = "$column <= '".DateUtil::getDatetime($time)."'";
                break;
        }

        return ['where' => $where];
    }

    /**
     * Returns DQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array Doctrine Query where clause and parameters.
     */
    public function getDql($field, $op, $value)
    {
        if (array_search($op, $this->ops) === false || !$this->fieldExists($field)) {
            return '';
        }

        $type = 'point';
        if (preg_match('~^(year|month|week|day|hour|min):\s*(.*)$~i', $value, $res)) {
            $type = strtolower($res[1]);
            if (strlen($res[2]) == 4) {
                $res[2] = "01.01." . $res[2];
            }
            $time = strtotime($res[2]);
        } elseif (preg_match('~(year|month|week|day|hour|min|tomorrow)~', $value, $res)) {
            $type = strtolower($res[1]);
            $time = strtotime($value);
        } else {
            $time = strtotime($value);
        }

        $where = '';
        $params = [];
        $column = $this->getColumn($field);

        switch ($op) {
            case 'eq':
                if ($type != 'point') {
                    list($from, $to) = $this->makePeriod($time, $type);
                    $where = "($column >= ? AND $column < ?)";
                    $params[] = DateUtil::getDatetime($from);
                    $params[] = DateUtil::getDatetime($to);
                } else {
                    $where = "$column = ?";
                    $params[] = DateUtil::getDatetime($time);
                }
                break;

            case 'ne':
                if ($type != 'point') {
                    list($from, $to) = $this->makePeriod($time, $type);
                    $where = "($column < ? OR $column >= ?)";
                    $params[] = DateUtil::getDatetime($from);
                    $params[] = DateUtil::getDatetime($to);
                } else {
                    $where = "$column <> ?";
                    $params[] = DateUtil::getDatetime($time);
                }
                break;

            case 'gt':
                if ($type != 'point') {
                    list($from, $time) = $this->makePeriod($time, $type);
                }
                $where = "$column > ?";
                $params[] = DateUtil::getDatetime($time);
                break;

            case 'ge':
                $where = "$column >= ?";
                $params[] = DateUtil::getDatetime($time);
                break;

            case 'lt':
                $where = "$column < ?";
                $params[] = DateUtil::getDatetime($time);
                break;

            case 'le':
                if ($type != 'point') {
                    list($from, $time) = $this->makePeriod($time, $type);
                }
                $where = "$column <= ?";
                $params[] = DateUtil::getDatetime($time);
                break;
        }

        return ['where' => $where, 'params' => $params];
    }
}
