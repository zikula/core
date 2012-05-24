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
class FilterUtil extends FilterUtil_AbstractBase
{
    /**
     * The Input variable name.
     *
     * @var string
     */
    private $_varname;

    /**
     * Plugin object.
     *
     * @var array
     */
    private $_plugin;

    /**
     * Filter object holder.
     *
     * @var array
     */
    private $_obj;

    /**
     * Filter string holder.
     *
     * @var array
     */
    private $_filter;

    /**
     * Filter SQL holder.
     *
     * @var array
     */
    private $_sql;

    /**
     * Filter DQL holder.
     *
     * @var array
     */
    private $_dql;

    /**
     * Constructor.
     *
     * Argument $args may contain:
     *  plugins: Set of plugins to load.
     *  varname: Name of filters in $_REQUEST. Default: filter.
     *
     * @param string                $module Module name.
     * @param string|Doctrine_Table $table  Table name.
     * @param array                 $args   Mixed arguments.
     */
    public function __construct($module, $table, $args = array())
    {
        $this->setVarName('filter');

        $args['module'] = $module;
        $args['table'] = $table;
        parent::__construct(new FilterUtil_Config($args));

        $this->_plugin = new FilterUtil_PluginManager($this->getConfig(), array('default' => array()));

        if (isset($args['plugins'])) {
            $this->_plugin->loadPlugins($args['plugins']);
        }
        if (isset($args['restrictions'])) {
            $this->_plugin->loadRestrictions($args['restrictions']);
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

        $this->_varname = $name;

        return true;
    }

    /**
     * Get name of the input variable.
     *
     * @return string Name of the variable.
     */
    public function getVarName()
    {
        return $this->_varname;
    }

    /**
     * Get plugin manager class.
     *
     * @return FilterUtil_PluginManager
     */
    public function getPlugin()
    {
        return $this->_plugin;
    }

    //++++++++++++++++ Object handling +++++++++++++++++++

    /**
     * strip brackets around a filterstring.
     *
     * @param string $filter Filterstring.
     *
     * @return string Edited filterstring.
     */
    private function _stripBrackets($filter)
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
    private function _makeCondition($filter)
    {
        if (strpos($filter, ':')) {
            $parts = explode(':', $filter, 3);
        } elseif (strpos($filter, '^')) {
            $parts = explode('^', $filter, 3);
        }

        $obj = array(
                'field' => false,
                'op' => false,
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

        $obj = $this->_plugin->replace($obj['field'], $obj['op'], $obj['value']);

        return $obj;
    }

    /**
     * Help function to generate an object out of a string.
     *
     * @param string $filter Filterstring.
     *
     * @return array Filter object.
     */
    private function _genObjectRecursive($filter)
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
                        $sub = $this->_makeCondition($string);
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
                        $sub = $this->_makeCondition($string);
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
                        $sub = $this->_genObjectRecursive($string);
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
            $sub = $this->_makeCondition($string);
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
        $this->_obj = $this->_genObjectRecursive($this->getFilter());
    }

    /**
     * Get the filter object
     *
     * @return array Filter object
     */
    public function getObject()
    {
        if (!isset($this->_obj) || !is_array($this->_obj)) {
            $this->genObject();
        }

        return $this->_obj;
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
        $filterStr = FormUtil::getPassedValue($this->_varname, '');
        if (!empty($filterStr)) {
            $filter[] = $filterStr;
        }

        // Get filter1 ... filterN
        while (true) {
            $filterURLName = $this->_varname . "$i";
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
        if (!isset($this->_filter) || empty($this->_filter)) {
            $filter = $this->getFiltersFromInput();
            if (is_array($filter) && count($filter) > 0) {
                $this->_filter = "(" . implode(')*(', $filter) . ")";
            }
        }

        if ($this->_filter == '()') {
            $this->_filter = '';
        }

        return $this->_filter;
    }

    /**
     * Set filterstring.
     *
     * @param mixed $filter Filter string or array.
     *
     * @return void
     */
    public function setFilter($filter)
    {
        if (is_array($filter)) {
            $this->_filter = "(" . implode(')*(', $filter) . ")";
        } else {
            $this->_filter = $filter;
        }

        $this->_obj = false;
        $this->_sql = false;
    }

    /**
     * Add filter string.
     *
     * Adds a filter or an array of filters.
     * If filter does not begin with "," or "*" append it as "and".
     *
     * @param mixed $filter Filter string or array.
     *
     * @return void
     */
    public function addFilter($filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $tmp) {
                $this->addFilter($tmp);
            }
        } elseif (substr($filter, 0, 1) == ',' || substr($filter, 0, 1) == '*') {
            $this->_filter .= $filter;
        } else {
            $this->andFilter($filter);
        }
    }

    /**
     * Add filter string with "AND".
     *
     * @param mixed $filter Filter string or array.
     *
     * @return void
     */
    public function andFilter($filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $tmp) {
                $this->andFilter($tmp);
            }
        } elseif (substr($filter, 0, 1) == ',') {
            $this->_filter .= $filter;
        } elseif (substr($filter, 0, 1) == '*') {
            $this->_filter .= ',' . (substr($filter, 1));
        } else {
            $this->_filter .= ',' . ($filter);
        }
    }

    /**
     * Add filter string with "OR".
     *
     * @param mixed $filter Filter string or array.
     *
     * @return void
     */
    public function orFilter($filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $tmp) {
                $this->orFilter($tmp);
            }
        } elseif (substr($filter, 0, 1) == '*') {
            $this->_filter .= $filter;
        } elseif (substr($filter, 0, 1) == ',') {
            $this->_filter .= '*' . (substr($filter, 1));
        } else {
            $this->_filter .= '*' . ($filter);
        }
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
    private function _genSqlRecursive($obj)
    {
        if (!is_array($obj) || count($obj) == 0) {
            return '';
        }

        if (isset($obj['field']) && !empty($obj['field'])) {
            $obj['value'] = DataUtil::formatForStore($obj['value']);
            $res = $this->_plugin->getSQL($obj['field'], $obj['op'], $obj['value']);
            $res['join'] = & $this->join;

            return $res;
        } else {
            $where = '';
            if (isset($obj[0]) && is_array($obj[0])) {
                $sub = $this->_genSqlRecursive($obj[0]);
                if (!empty($sub['where'])) {
                    $where .= $sub['where'];
                }
                unset($obj[0]);
            }

            foreach ($obj as $op => $tmp) {
                $op = strtoupper(substr($op, 0, 3)) == 'AND' ? 'AND' : 'OR';
                if (strtoupper($op) == 'AND' || strtoupper($op) == 'OR') {
                    $sub = $this->_genSqlRecursive($tmp);
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
        $this->_sql = $this->_genSqlRecursive($object);
    }

    /**
     * Get where/join SQL.
     *
     * @return array Array with where and join.
     */
    public function getSql()
    {
        if (!isset($this->_sql) || !is_array($this->_sql)) {
            $this->genSQL();
        }

        return $this->_sql;
    }

    //+++++++++++++++ SQL Handling +++++++++++++++++++++++++

    /**
     * Help function for enrich the Doctrine Query object with the filters from a Filter-object.
     *
     * @param array $obj Object array.
     *
     * @return array Doctrine Query where clause addition and parameters.
     */
    private function _genDqlRecursive($obj)
    {
        if (!is_array($obj) || count($obj) == 0) {
            return '';
        }

        if (isset($obj['field']) && !empty($obj['field'])) {
            $obj['value'] = DataUtil::formatForStore($obj['value']);
            $res = $this->_plugin->getDql($obj['field'], $obj['op'], $obj['value']);

            return $res;
        } else {
            $where = '';
            $params = array();
            if (isset($obj[0]) && is_array($obj[0])) {
                $sub = $this->_genDqlRecursive($obj[0]);
                if (!empty($sub)) {
                    $where .= $sub['where'];
                    if (isset($sub['params'])) {
                        $params = array_merge($params, $sub['params']);
                    }
                }
                unset($obj[0]);
            }
            foreach ($obj as $op => $tmp) {
                $op = strtoupper(substr($op, 0, 3)) == 'AND' ? 'AND' : 'OR';
                if (strtoupper($op) == 'AND' || strtoupper($op) == 'OR') {
                    $sub = $this->_genDqlRecursive($tmp);
                    if (!empty($sub)) {
                        $where .= ' ' . strtoupper($op) . ' ' . $sub['where'];
                        $params = array_merge($params, $sub['params']);
                    }
                }
            }
        }

        return array(
            'where'  => (empty($where) ? '' : "($where)"),
            'params' => $params
        );
    }

    /**
     * Enrich DQL.
     *
     * @param Doctrine_Query $query Doctrine Query Object.
     *
     * @return void
     */
    public function enrichQuery(Doctrine_Query $query)
    {
        $object = $this->getObject();
        $this->getConfig()->setDoctrineQuery($query, $object);

        $result = $this->_genDqlRecursive($object);

        if (is_array($result) && !empty($result['where'])) {
            $query->andWhere(substr($result['where'], 1, -1), $result['params']);
            $this->_dql = $result;
        }
    }

}
