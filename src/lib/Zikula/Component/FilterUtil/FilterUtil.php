<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Base as BaseExpr;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adds a Pagesetter like filter.
 */
class FilterUtil
{
    /**
     * Plugin object.
     *
     * @var array
     */
    private $pluginManager;

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
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $filterKey;

    /**
     * Constructor.
     *
     * @param PluginManager $pluginManager
     * @param Request       $request
     * @param string        $filterKey
     */
    public function __construct(PluginManager $pluginManager, Request $request = null, $filterKey = 'filter')
    {
        $this->pluginManager = $pluginManager;
        $this->request = $request;
        $this->filterKey = $filterKey;
    }

    /**
     * Factory method
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $plugins
     * @param array        $restrictions
     * @param Request      $request
     * @param string       $filterKey
     *
     * @return FilterUtil
     */
    public static function create(QueryBuilder $queryBuilder, array $plugins = [], array $restrictions = [], Request $request = null, $filterKey = 'filter')
    {
        $pluginManager = new PluginManager(new Config($queryBuilder), $plugins, $restrictions);

        return new self($pluginManager, $request, $filterKey);
    }

    /**
     * Get plugin manager class.
     *
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * Get all filters from Input
     *
     * @throws \LogicException
     *
     * @return array Array of filters
     */
    public function getFiltersFromInput()
    {
        if ($this->request === null) {
            throw new \LogicException('Request object not set.');
        }

        $i = 1;
        $filter = [];
        // TODO get filter via request object
        // Get unnumbered filter string
        $filterStr = $this->request->query->filter(
            $this->filterKey,
            '',
            false,
            FILTER_SANITIZE_STRING
        );

        if (!empty($filterStr)) {
            $filter[] = $filterStr;
        }

        // Get filter1 ... filterN
        while (true) {
            $filterURLName = $this->filterKey."$i";
            $filterStr = $this->request->query->filter(
                $filterURLName,
                '',
                false,
                FILTER_SANITIZE_STRING
            );

            if (empty($filterStr)) {
                break;
            }
            $filter[] = $filterStr;
            ++$i;
        }

        return $filter;
    }

    /**
     * Get filter string.
     *
     * @return string $filter Filter string
     */
    public function getFilter()
    {
        if (!isset($this->filter) || empty($this->filter)) {
            $filter = $this->getFiltersFromInput();
            if (is_array($filter) && count($filter) > 0) {
                $this->filter = "(".implode(')*(', $filter).")";
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
     * @param mixed $filter Filter string or array
     *
     * @return void
     */
    public function setFilter($filter)
    {
        if (is_array($filter)) {
            $this->filter = "(".implode(')*(', $filter).")";
        } else {
            $this->filter = $filter;
        }

        $this->filterExpr = false;
    }

    /**
     * Add filter string.
     *
     * Adds a filter or an array of filters.
     * If filter does not begin with "," or "*" append it as "and".
     *
     * @param mixed $filter Filter string or array
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
     * @param mixed $filter Filter string or array
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
            $this->filter .= ','.(substr($filter, 1));
        } else {
            $this->filter .= ','.($filter);
        }
    }

    /**
     * Add filter string with "OR".
     *
     * @param mixed $filter Filter string or array
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
            $this->filter .= '*'.(substr($filter, 1));
        } else {
            $this->filter .= '*'.($filter);
        }
    }

    /**
     * Create a condition object out of a string.
     *
     * @param string $filter Condition string
     *
     * @return array Condition object
     */
    private function makeCondition($filter)
    {
        if (strpos($filter, ':')) {
            $parts = explode(':', $filter, 3);
        } elseif (strpos($filter, '^')) {
            $parts = explode('^', $filter, 3);
        }

        $con = [
            'field' => false,
            'op' => false,
            'value' => false
        ];

        if (isset($parts) && is_array($parts) && count($parts) > 2) {
            $con['field'] = $parts[0];
            $con['op'] = $parts[1];
            if ($this->request !== null && substr($parts[2], 0, 1) == '$') {
                $value = $this->request->filter(substr($parts[2], 1));
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

        $con = $this->pluginManager->replace($con['field'], $con['op'], $con['value']);

        return $this->pluginManager->getExprObj($con['field'], $con['op'], $con['value']);
    }

    /**
     * if $a and $b are of the same type the parts
     * of $b are added to $a.
     *
     * @param BaseExpr $a expression object to add to
     * @param mixed    $b anything to add to $a
     */
    private function addBtoA(BaseExpr $a, $b)
    {
        if (($a instanceof Andx && $b instanceof Andx) || ($a instanceof Orx && $b instanceof Orx)) {
            $a->addMultiple($b->getParts());
        } else {
            $a->add($b);
        }
    }

    /**
     * Help function to generate an object out of a string.
     *
     * @param string $filter Filter string
     *
     * @throws \InvalidArgumentException
     *
     * @return array Filter[] object
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
         * Build a tree with an OR object as root (if one exists), AND Objects as children of the OR
         * and conditions as leafs. Handle expressions in brackets like normal conditions (parsed
         * recursively). Using Doctrine expression objects
         */
        $filterlen = strlen($filter);
        for ($i = 0; $i < $filterlen; $i++) {
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
                        $or = new Orx();
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
                        $and = new Andx();
                        if ($or !== null) {
                            $this->addBtoA($or, $and);
                        }
                        $op = $and;
                    }
                    $this->addBtoA($and, $con);
                    $string = '';
                    break;
                case '(': // Subquery
                    $level++;
                    while ($level != 0 && $i <= strlen($filter)) {
                        // get closing bracket
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
                throw new \InvalidArgumentException('Malformed filter string');
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
     * @return array Filter[]
     */
    public function genFilterExpr()
    {
        // TODO check filter string (via regex?)
        $this->filterExpr = $this->genFilterExprRecursive($this->getFilter());

        return $this->filterExpr;
    }

    /**
     * Enrich Querybuilder with where clause
     */
    public function enrichQuery()
    {
        $qb = $this->pluginManager->getConfig()->getQueryBuilder();
        $filterExpr = $this->genFilterExpr();
        if ($filterExpr !== null) {
            $qb->where($filterExpr);
        }
    }

    // ---------------- String to Querybuilder handling ---------------------
}
