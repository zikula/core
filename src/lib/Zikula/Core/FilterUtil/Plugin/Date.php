<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package FilterUtil
 * @subpackage Filter
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil\Plugin;

use Zikula\Core\FilterUtil;
use DataUtil;

/**
 * FilterUtil date handler plugin
 */
class Date extends FilterUtil\AbstractBuildPlugin implements FilterUtil\ReplaceInterface
{
    /**
     * Constructor.
     *
     * @param array $fields
     *            Set of fields to use, see setFields() (optional) (default=null).
     * @param array $ops
     *            Operators to enable, see activateOperators() (optional) (default=null).
     * @param bool $default
     *            set the plugin to default (optional) (default=false).
     */
    public function __construct($fields = null, $ops = null, $default = false)
    {
        parent::__construct($fields, $ops, $default);
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return array(
            'eq',
            'ne',
            'gt',
            'ge',
            'lt',
            'le'
        );
    }

    /**
     * Replace field's value.
     *
     * @param string $field
     *            Field name.
     * @param string $op
     *            Filter operator.
     * @param string $value
     *            Filter value.
     *            
     * @return array New filter set.
     */
    public function replace($field, $op, $value)
    {
        // First check if this plugin have to work with this field
        if (array_search($field, $this->fields) === false) {
            return array(
                $field,
                $op,
                $value
            ); // If not, return given value
        }
        
        // Now, work!
        // convert to unix timestamp
        if (($date = $this->dateConvert($value)) === false) {
            return false;
        }
        
        return array(
            $field,
            $op,
            $date
        );
    }

    /**
     * Convert the date to a more useful format.
     *
     * @param string $date
     *            Date string.
     *            
     * @return string Converted date
     */
    protected function dateConvert($date)
    {
        if (strptime($date, "%d.%m.%Y %H:%M:%S") !== false) {
            $arr = strptime($date, "%d.%m.%Y %H:%M:%S");
            $time = DateUtil::buildDatetime($arr['tm_year'], $arr['tm_mon'], $arr['tm_monday'], 
                $arr['tm_hour'], $arr['tm_min'], $arr['tm_sec']);
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
     * year: That year.
     * month: That month.
     * day: That day.
     * tomorrow: The day after that day.
     * hour: That hour.
     * min/minute: That minute.
     * Returns an array(from, to).
     *
     * @param string $date
     *            Date string.
     * @param string $type
     *            Period type.
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
                $from = mktime($datearray['hours'], 0, 0, $datearray['mon'], $datearray['mday'], 
                    $datearray['year']);
                $to = $from + 3600;
                break;
            
            case 'min':
            case 'minute':
                $from = mktime($datearray['hours'], $datearray['minutes'], 0, $datearray['mon'], 
                    $datearray['mday'], $datearray['year']);
                $to = $from + 60;
                break;
        }
        
        return array(
            $from,
            $to
        );
    }

    /**
     * Get the Doctrine2 expression object
     *
     * @param string $field
     *            Field name.
     * @param string $op
     *            Operator.
     * @param string $value
     *            Value.
     *            
     * @return Expr\Base Doctrine2 expression
     */
    public function getExprObj($field, $op, $value)
    {    
        $config = $this->config;
        $column = $config->addAliasTo($field);
        $config->testFieldExists($column);
        $expr = $config->getQueryBuilder()->expr();
        
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

        switch ($op) {
            case 'eq':
                if ($type != 'point') {
                    list ($from, $to) = $this->makePeriod($time, $type);
                    
                    return $expr->andX($expr->gte($column, $config->toParam($from, 'date', $field)), 
                        $expr->lt($column, $config->toParam($to, 'date', $field)));
                } else {
                    return $expr->eq($column, $config->toParam($time, 'date', $field));
                }
            
            case 'ne':
                if ($type != 'point') {
                    list ($from, $to) = $this->makePeriod($time, $type);
                    
                    return $expr->orX($expr->lt($column, $config->toParam($from, 'date', $field)), 
                        $expr->gte($column, $config->toParam($to, 'date', $field)));
                } else {
                    return $expr->neq($column, $config->toParam($time, 'date', $field));
                }
            
            case 'gt':
                if ($type != 'point') {
                    list ($from, $time) = $this->makePeriod($time, $type);
                }
                
                return $expr->gt($column, $config->toParam($time, 'date', $field));
            
            case 'ge':
                
                return $expr->gte($column, $config->toParam($time, 'date', $field));
            
            case 'lt':
                
                return $expr->lt($column, $config->toParam($time, 'date', $field));
            
            case 'le':
                if ($type != 'point') {
                    list ($from, $time) = $this->makePeriod($time, $type);
                }
                return $expr->lte($column, $config->toParam($time, 'date', $field));
        }
    }
}
