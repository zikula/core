<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Adds a Pagesetter like filter.
 */
class FilterUtil extends FilterUtil_Common
{

    /**
     * The Input variable name.
     *
     * @var string
     */
    private $varname;

    /**
     * Plugin object.
     *
     * @var array
     */
    private $plugin;

    /**
     * Filter object holder.
     *
     * @var array
     */
    private $obj;

    /**
     * Filter string holder.
     *
     * @var array
     */
    private $filter;

    /**
     * Filter SQL holder.
     *
     * @var array
     */
    private $sql;
    
    /**
     * Filter DQL holder.
     *
     * @var array
     */
    private $dql;

    /**
     * Constructor.
     *
     * Argument $args may contain:
     *  plugins: Set of plugins to load.
     *  varname: Name of filters in $_REQUEST. Default: filter.
     *
     * @param string                 $module Module name.
     * @param string|Doctrine_Table $table  Table name.
     * @param array                  $args   Mixed arguments.
     */
    public function __construct($module, $table, $args = array())
    {
        $this->setVarName('filter');

        $args['module'] = $module;
        $args['table']  = $table;
        parent::__construct($args);

        $config = array();
        $this->addCommon($config);

        $this->plugin = new FilterUtil_Plugin($args, array('default' => array()));

        if (isset($args['plugins'])) {
            $this->plugin->loadPlugins($args['plugins']);
        }
        if (isset($args['varname'])) {
            $this->setVarName($args['varname']);
        }

        return $this;
    }

    /**
     * Set name of input variable of filter.
     *
     * @param string $name Name of input variable.
     *
     * @return bool true on success, false otherwise.
     */
    public function setVarName($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $this->varname = $name;

        return true;
    }

    /**
     * Get plugin manager class.
     *
     * @return FilterUtil_Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }


    //++++++++++++++++ Object handling +++++++++++++++++++

    /**
     * strip brackets around a filterstring.
     *
     * @param string $filter Filterstring.
     *
     * @return string Edited filterstring.
     */
    private function stripBrackets($filter)
    {
        if (substr($filter, 0, 1) == '(' && substr($filter, -1) == ')') {
            return substr($filter, 1, -1);
        }

        return $filter;
    }

    /**
     * Create a condition object out of a string.
     *
     * @param string $filter Condition string.
     *
     * @return array Condition object.
     */
    private function makeCondition($filter)
    {
        if (strpos($filter, ':')) {
            $parts = explode(':', $filter, 3);
        } elseif (strpos($filter, '^')) {
            $parts = explode('^', $filter, 3);
        }

        $obj = array(
                     'field' => false,
                     'op'    => false,
                     'value' => false
                    );

        if (isset($parts[2]) && substr($parts[2], 0, 1) == '$') {
            $value = FormUtil::getPassedValue(substr($parts[2], 1), null);
            if (empty($value) && !is_numeric($value)) {
                return false;
            }
            $obj['value'] = $value;
        } elseif (isset($parts) && is_array($parts) && count($parts) > 2) {
            $obj['value'] = $parts[2];
        }

        if (isset($parts) && is_array($parts) && count($parts) > 1) {
            $obj['field'] = $parts[0];
            $obj['op'] = $parts[1];
        }

        if (!$obj['field'] || !$obj['op']) {
            return false; // invalid condition
        }

        $obj = $this->plugin->replace($obj['field'], $obj['op'], $obj['value']);

        return $obj;
    }

    /**
     * Help function to generate an object out of a string.
     *
     * @param string $filter Filterstring.
     *
     * @return array Filter object.
     */
    private function genObjectRecursive($filter)
    {
        $obj = array();
        $string = '';
        $cycle = 0;
        $op = 0;
        $level = 0;
        $sub = false;
        for ($i = 0; $i < strlen($filter); $i++) {
            $c = substr($filter, $i, 1);
            switch ($c) {
                case ',': // Operator: AND
                    if (!empty($string)) {
                        $sub = $this->makeCondition($string);
                        if ($sub != false && count($sub) > 0) {
                            $obj[$op] = $sub;
                            $sub = false;
                        }
                    }
                    if (count($obj) > 0) {
                        $op = 'AND' . $cycle++;
                    }
                    $string = '';
                    break;
                case '*': // Operator: OR
                    if (!empty($string)) {
                        $sub = $this->makeCondition($string);
                        if ($sub != false && count($sub) > 0) {
                            $obj[$op] = $sub;
                            $sub = false;
                        }
                    }
                    if (count($obj) > 0) {
                        $op = 'OR' . $cycle++;
                    }
                    $string = '';
                    break;
                case '(': // Subquery
                    $level++;
                    while ($level != 0 && $i <= strlen($filter)) {
                        // get end bracket
                        $i++;
                        $c = substr($filter, $i, 1);
                        switch ($c) {
                            case '(':
                                $level++;
                                break;
                            case ')':
                                $level--;
                                break;
                        }
                        if ($level > 0) {
                            $string .= $c;
                        }
                    }
                    if (!empty($string)) {
                        $sub = $this->genObjectRecursive($string);
                        if ($sub != false && count($sub) > 0) {
                            $obj[$op] = $sub;
                            $sub = false;
                        }
                    }
                    $string = '';
                    break;
                default:
                    $string .= $c;
                    break;
            }
        }

        if (!empty($string)) {
            $sub = $this->makeCondition($string);
            if ($sub != false && count($sub) > 0) {
                $obj[$op] = $sub;
                $sub = false;
            }
        }

        return $obj;
    }

    /**
     * Generate the filter object from a string.
     *
     * @return void
     */
    public function genObject()
    {
        $this->obj = $this->genObjectRecursive($this->getFilter());
    }

    /**
     * Get the filter object
     *
     * @return array Filter object
     */
    public function GetObject()
    {
        if (!isset($this->obj) || !is_array($this->obj)) {
            $this->genObject();
        }

        return $this->obj;
    }


    //---------------- Object handling ---------------------
    //++++++++++++++++ Filter handling +++++++++++++++++++++

    /**
     * Get all filters from Input
     *
     * @return array Array of filters
     */
    public function getFiltersFromInput()
    {
        $i = 1;
        $filter = array();

        // Get unnumbered filter string
        $filterStr = FormUtil::getPassedValue($this->varname, '');
        if (!empty($filterStr)) {
            $filter[] = $filterStr;
        }

        // Get filter1 ... filterN
        while (true) {
            $filterURLName = $this->varname . "$i";
            $filterStr = FormUtil::getPassedValue($filterURLName, '');

            if (empty($filterStr)) {
                break;
            }

            $filter[] = $filterStr;
            ++$i;
        }

        return $filter;
    }

    /**
     * Get filterstring.
     *
     * @return string $filter Filterstring.
     */
    public function getFilter()
    {
        if (!isset($this->filter) || empty($this->filter)) {
            $filter = $this->getFiltersFromInput();
            if (is_array($filter) && count($filter) > 0) {
                $this->filter = "(" . implode(')*(', $filter) . ")";
            }
        }

        if ($this->filter == '()') {
            $this->filter = '';
        }

        return $this->filter;
    }

    /**
     * Set filterstring.
     *
     * @param mixed $filter Filter string or array.
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        if (is_array($filter)) {
            $this->filter = "(" . implode(')*(', $filter) . ")";
        } else {
            $this->filter = $filter;
        }

        $this->obj = false;
        $this->sql = false;

        return $this;
    }


    //--------------- Filter handling ----------------------
    //+++++++++++++++ SQL Handling +++++++++++++++++++++++++

    /**
     * Help function for generate the filter SQL from a Filter-object.
     *
     * @param array $obj Object array.
     *
     * @return array Where and Join sql.
     */
    private function genSqlRecursive($obj)
    {
        if (!is_array($obj) || count($obj) == 0) {
            return '';
        }

        if (isset($obj['field']) && !empty($obj['field'])) {
            $obj['value'] = DataUtil::formatForStore($obj['value']);
            $res = $this->plugin->getSQL($obj['field'], $obj['op'], $obj['value']);
            $res['join'] = & $this->join;
            return $res;
        } else {
            $where = '';
            if (isset($obj[0]) && is_array($obj[0])) {
                $sub = $this->genSqlRecursive($obj[0]);
                if (!empty($sub['where'])) {
                    $where .= $sub['where'];
                }
                unset($obj[0]);
            }
            foreach ($obj as $op => $tmp) {
                $op = strtoupper(substr($op, 0, 3)) == 'AND' ? 'AND' : 'OR';
                if (strtoupper($op) == 'AND' || strtoupper($op) == 'OR') {
                    $sub = $this->genSqlRecursive($tmp);
                    if (!empty($sub['where'])) {
                        $where .= ' ' . strtoupper($op) . ' ' . $sub['where'];
                    }
                }
            }
        }

        return array(
                     'where' => (empty($where) ? '' : "(\n $where \n)"),
                     'join' => &$this->join
                    );
    }

    /**
     * Generate where/join SQL.
     *
     * @return void
     */
    public function genSql()
    {
        $object = $this->getObject();
        $this->sql = $this->genSqlRecursive($object);
    }

    /**
     * Get where/join SQL.
     *
     * @return array Array with where and join.
     */
    public function getSql()
    {
        if (!isset($this->sql) || !is_array($this->sql)) {
            $this->genSQL();
        }

        return $this->sql;
    }
    
    //+++++++++++++++ SQL Handling +++++++++++++++++++++++++
    
/**
     * Help function for enrich the Doctrine Query object with the filters from a Filter-object.
     *
     * @param Doctrine_Query $query Doctrine Query object.
     * @param array $obj Object array.
     *
     * @return array Doctrine Query where clause addition and parameters.
     */
    private function _genDqlRecursive(Doctrine_Query $query, $obj)
    {
        if (!is_array($obj) || count($obj) == 0) {
            return '';
        }

        if (isset($obj['field']) && !empty($obj['field'])) {
            $obj['value'] = DataUtil::formatForStore($obj['value']);
            $res = $this->plugin->getDql($query, $obj['field'], $obj['op'], $obj['value']);
            return $res;
        } else {
            $where = '';
            $params = array();
            if (isset($obj[0]) && is_array($obj[0])) {
                $sub = $this->_genDqlRecursive($query, $obj[0]);
                if (!empty($sub)) {
                    $where .= $sub['where'];
                    $params = array_merge($params, $sub['params']);
                }
                unset($obj[0]);
            }
            foreach ($obj as $op => $tmp) {
                $op = strtoupper(substr($op, 0, 3)) == 'AND' ? 'AND' : 'OR';
                if (strtoupper($op) == 'AND' || strtoupper($op) == 'OR') {
                    $sub = $this->_genDqlRecursive($query, $tmp);
                    if (!empty($sub)) {
                        $where .= ' ' . strtoupper($op) . ' ' . $sub['where'];
                        $params = array_merge($params, $sub['params']);
                    }
                }
            }
        }

        return array('where' => $where, 'params' => $params);
    }
    
    /**
     * Enrich DQL.
     * 
     * @param Doctrine_Query $query Doctrine Query Object
     * 
     * @return void
     */
    public function enrichQuery(Doctrine_Query $query)
    {
        $object = $this->getObject();
        $result = $this->_genDqlRecursive($query, $object);
        
        if (is_array($result) && !empty($result['where'])) {
            $query->AndWhere($result['where'], $result['params']);
            $this->dql = $result;
        }
    }
}
