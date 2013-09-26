<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula\Core\FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;

/**
 * Adds a Pagesetter like filter.
 */
class FilterUtil extends AbstractBase
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
     * FilterExpression object holder.
     *
     * @var array
     */
    private $filterExpr;

    /**
     * Filter string holder.
     *
     * @var array
     */
    private $filter;
    
    /**
     * Request object to get the filter 
     * 
     * @var \Zikula_Request_Http
     */
    private $request;

    /**
     * Constructor.
     *
     * Argument $args may contain:
     * plugins: Set of plugins to load.
     * varname: Name of filters in $_REQUEST. Default: filter.
     * restrictions: Array of allowed operators per field in the form "field's name => operator array".
     *
     * @param string $module
     *            Module name.
     * @param string|Doctrine_Table $table
     *            Table name.
     * @param array $args
     *            Mixed arguments.
     */
    public function __construct(\Doctrine\ORM\EntityManager $entityMangager, QueryBuilder $qb, $args = array())
    {
        $this->setVarName('filter');
        
        parent::__construct(new Config($entityMangager, $qb, $args));
        
        $this->plugin = new PluginManager($this->getConfig(), 
            isset($args['plugins']) ? $args['plugins'] : array(), 
            isset($args['restrictions']) ? $args['restrictions'] : null);
        
        if (isset($args['varname'])) {
            $this->setVarName($args['varname']);
        }
        if (isset($args['request'])) {
            $this->setRequest($args['request']);
        }
        
        return $this; // is this still required?
    }
    
    /**
     * set the Request Object
     * 
     * @var \Zikula_Request_Http
     */
    public function setRequest(\Zikula_Request_Http $request) {
        $this->request = $request;
    }

    /**
     * Set name of input variable of filter.
     *
     * @param string $name
     *            Name of input variable.
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
     * Get name of the input variable.
     *
     * @return string Name of the variable.
     */
    public function getVarName()
    {
        return $this->varname;
    }

    /**
     * Get plugin manager class.
     *
     * @return PluginManager
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
    
    // ++++++++++++++++ Filter handling +++++++++++++++++++++
    
    /**
     * Get all filters from Input
     *
     * @return array Array of filters
     */
    public function getFiltersFromInput()
    {
        if ($this->request === null) {
            throw new \Exception('Request object not set.');
        }
        
        $i = 1;
        $filter = array();
        
        //TODO get filter via request object
        // Get unnumbered filter string
        $filterStr = $this->request->query->filter($this->varname, '', false, FILTER_SANITIZE_STRING);
        if (!empty($filterStr)) {
            $filter[] = $filterStr;
        }
        
        // Get filter1 ... filterN
        while (true) {
            $filterURLName = $this->varname . "$i";
            $filterStr = $this->request->query->filter($filterURLName, '', false, FILTER_SANITIZE_STRING);
            
            if (empty($filterStr)) {
                break;
            }
            
            $filter[] = $filterStr;
            ++ $i;
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
     * @param mixed $filter
     *            Filter string or array.
     *            
     * @return void
     */
    public function setFilter($filter)
    {
        if (is_array($filter)) {
            $this->filter = "(" . implode(')*(', $filter) . ")";
        } else {
            $this->filter = $filter;
        }
        
        $this->filterExpr = false;
        $this->sql = false;
    }

    /**
     * Add filter string.
     *
     * Adds a filter or an array of filters.
     * If filter does not begin with "," or "*" append it as "and".
     *
     * @param mixed $filter
     *            Filter string or array.
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
            $this->filter .= $filter;
        } else {
            $this->andFilter($filter);
        }
    }

    /**
     * Add filter string with "AND".
     *
     * @param mixed $filter
     *            Filter string or array.
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
            $this->filter .= $filter;
        } elseif (substr($filter, 0, 1) == '*') {
            $this->filter .= ',' . (substr($filter, 1));
        } else {
            $this->filter .= ',' . ($filter);
        }
    }

    /**
     * Add filter string with "OR".
     *
     * @param mixed $filter
     *            Filter string or array.
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
            $this->filter .= $filter;
        } elseif (substr($filter, 0, 1) == ',') {
            $this->filter .= '*' . (substr($filter, 1));
        } else {
            $this->filter .= '*' . ($filter);
        }
    }
    
    // --------------- Filter handling ----------------------
    // ++++++++++++++++ String to Querybuilder handling +++++++++++++++++++
    /**
     * Create a condition object out of a string.
     *
     * @param string $filter
     *            Condition string.
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
        
        $con = array(
            'field' => false,
            'op' => false,
            'value' => false
        );
        
        if (isset($parts) && is_array($parts) && count($parts) > 2) {
            $con['field'] = $parts[0];
            $con['op'] = $parts[1];
            
            if (substr($parts[2], 0, 1) == '$') {
                $value = FormUtil::getPassedValue(substr($parts[2], 1), null);
                // !is_numeric because empty(0) == false
                if (empty($value) && !is_numeric($value)) {
                    return null;
                }
                $con['value'] = $value;
            } else {
                $con['value'] = $parts[2];
            }
        }
        
        if (!$con['field'] || !$con['op']) {
            return null; // invalid condition
        }
        
        $con = $this->plugin->replace($con['field'], $con['op'], $con['value']);
        
        return $this->plugin->getExprObj($con['field'], $con['op'], $con['value']);
    }

    /**
     * if $a and $b are of the same type the parts
     * of $b are added to $a.
     *
     * @param Base  $a expression object to add to
     * @param mixed $b anything to add to $a
     */
    private function addBtoA($a, $b) {
        if ( ($a instanceof Andx && $b instanceof Andx) ||
                ($a instanceof Orx && $b instanceof Orx)) {
            $a->addMultiple($b->getParts());
        } else {
            $a->add($b);
        }
    }
    
    /**
     * Help function to generate an object out of a string.
     *
     * @param string $filter
     *            Filterstring.
     *            
     * @return array Filter object.
     */
    private function genFilterExprRecursive($filter)
    {
        $or = null;
        $and = null;
        $subexpr = null;
        $op = $or;
        $string = '';
        $level = 0;
        $con = false;
        
        /*
         * Build a tree with an OR object as root (if one excists), 
         * AND Objects as childs of the OR and conditions as leafs. 
         * Handle expressions in brackets like normal conditions (parsed recursivly). 
         * Using Doctrine2 expression objects
         */
        $filterlen = strlen($filter);
        for ($i = 0; $i < $filterlen; $i ++) {
            $c = substr($filter, $i, 1);
            switch ($c) {
                case '*': // Operator: OR
                    $con = $this->makeCondition($string);
                    
                    if ($con === null) {
                        if ($subexpr !== null) {
                            $con = $subexpr;
                            $subexpr = null;
                        } else {
                            $string = '';
                            break;
                        }
                    }
                    
                    if ($or === null) { // make new or Object
                        $or = new Expr\Orx();
                        if ($and !== null) { // add existing and
                            $this->addBtoA($or, $and);
                        }
                    }
                    if ($op === null) {
                        $op = $or;
                    }
                    
                    $this->addBtoA($op, $con); // add condition to last operator object
                    
                    $op = $or;
                    $and = null;
                    
                    $string = '';
                    break;
                
                case ',': // Operator: AND
                    $con = $this->makeCondition($string);
                    
                    if ($con === null) {
                        if ($subexpr !== null) {
                            $con = $subexpr;
                            $subexpr = null;
                        } else {
                            $string = '';
                            break;
                        }
                    }
                    
                    if ($and == null) {
                        $and = new Expr\Andx();
                        if ($or !== null) {
                            $this->addBtoA($or, $and);
                        }
                        $op = $and;
                    }
                    $this->addBtoA($and, $con);
                    
                    $string = '';
                    break;
                
                case '(': // Subquery
                    $level ++;
                    while ($level != 0 && $i <= strlen($filter)) {
                        // get closing bracket
                        $i ++;
                        $c = substr($filter, $i, 1);
                        switch ($c) {
                            case '(':
                                $level ++;
                                break;
                            case ')':
                                $level --;
                                break;
                        }
                        if ($level > 0) {
                            $string .= $c;
                        }
                    }
                    if (!empty($string)) {
                        $subexpr = $this->genFilterExprRecursive($string);
                    }
                    $string = '';
                    break;
                
                default:
                    $string .= $c;
                    break;
            }
        }

        $con = $this->makeCondition($string);
        if ($con === null) {
            if ($subexpr !== null) {
                $con = $subexpr;
                $subexpr = null;
            }
        }
        
        if ($op !== null) {
            $this->addBtoA($op, $con);
        } else {
            if ($subexpr !== null) {
                throw new InvalidArgumentException('Malformed filter string');
            }
            return $con;
        }
        
        if ($or !== null) {
            return $or;
        }
        if ($and !== null) {
            return $and;
        }
        if ($subexpr !== null) {
            return $subexpr;
        }
        
        return null;
    }

    /**
     * Generate the filter object from a string.
     *
     * @return void
     */
    public function genFilterExpr()
    {
        // TODO check filter string (via regex?)
        $this->filterExpr = $this->genFilterExprRecursive($this->getFilter());
        return $this->filterExpr;
    }

    /**
     * Enrich Querybuilder with where clause
     *
     * @param Doctrine_Query $query
     *            Doctrine Query Object.
     *            
     * @return void
     */
    public function enrichQuery()
    {
        $qb = $this->config->getQueryBuilder();
        $filterExpr = $this->genFilterExpr();
        
        if ($filterExpr !== null) {
            $qb->where($filterExpr);
        }
    }

    // ---------------- String to Querybuilder handling ---------------------
}
