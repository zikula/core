<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula extension of the Doctrine_Table with utility methods.
 */
class Zikula_Doctrine_Table extends Doctrine_Table
{
    /**
     * Getter of the internal tablename.
     *
     * @return string Internal table name.
     */
    public function getInternalTableName()
    {
        $format = $this->_conn->getAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT);
        $format = str_replace('%s', '', $format);

        $tableName = $this->getTableName();
        return str_replace($format, '', $tableName);
    }

    /**
     * Select and return a field value.
     *
     * @param string $field The name of the field we wish to marshall.
     * @param string $where The where clause (optional) (default='').
     *
     * @return string The resulting field value.
     */
    public function selectField($field, $where = '')
    {
        // creates the query instance
        $q = $this->selectFieldQuery($field, $where);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return a field by a column value.
     *
     * @param string  $field  The field we wish to select.
     * @param integer $value  The value we wish to select with.
     * @param string  $column The column to use (optional) (default='id').
     *
     * @return mixed The resulting field value.
     */
    public function selectFieldBy($field, $value, $column = 'id')
    {
        // creates the query instance
        $q = $this->selectFieldQuery($field);

        $q->where($this->buildFindByWhere($column), (array)$value);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return an array of field values.
     *
     * @param string  $field    The name of the field we wish to marshall.
     * @param string  $where    The where clause (optional) (default='').
     * @param string  $orderBy  The orderby clause (optional) (default='').
     * @param boolean $distinct Whether or not to add a 'DISTINCT' clause (optional) (default=false).
     * @param string  $assocKey The key field to use to build the associative index (optional) (default='').
     *
     * @return array The resulting array of field values.
     */
    public function selectFieldArray($field, $where = '', $orderBy = '', $distinct = false, $assocKey = '')
    {
        if (!empty($assocKey) && !$this->hasField($assocKey)) {
            $assocKey = '';
        }

        // creates the query instance
        $q = $this->selectFieldQuery($field, $where, $orderBy, $distinct, $assocKey);

        if (!$assocKey) {
            $result = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            foreach ($result as $k => $v) {
                $result[$k] = $v[$field];
            }
        } else {
            $result = $q->execute()->toKeyValueArray($assocKey, $field);
        }

        return $result;
    }
    
    /**
     * Select and return an array of field values by a column value.
     *
     * @param string $field   The field we wish to select.
     * @param string $value   The value we wish to select with.
     * @param string $column  The column to use (optional) (default='id').
     * @param string $orderBy The orderby clause (optional) (default='').
     *
     * @return array The resulting field array.
     */
    public function selectFieldArrayBy($field, $value, $column = 'id', $orderBy = '')
    {
        // creates the query instance
        $q = $this->selectFieldQuery($field, '', $orderBy);

        $q->where($this->buildFindByWhere($column), (array)$value);

        $result = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($result as $k => $v) {
            $result[$k] = $v[$field];
        }

        return $result;
    }

    /**
     * Field Query creation.
     *
     * @param string  $field    The name of the field we wish to marshall.
     * @param string  $where    The where clause (optional) (default='').
     * @param string  $orderBy  The orderby clause (optional) (default='').
     * @param boolean $distinct Whether or not to add a 'DISTINCT' clause (optional) (default=false).
     * @param string  $assocKey The key field to use to build the associative index (optional) (default='').
     *
     * @return Doctrine_Query The resulting query object.
     */
    public function selectFieldQuery($field, $where = '', $orderBy = '', $distinct = false, $assocKey = '')
    {
        $queryAlias = 'dctrn_find';

        // validate the associative index
        if (!empty($assocKey) && $this->hasField($assocKey)) {
            $queryAlias .= ($assocKey ? " INDEXBY $assocKey" : '');
        } else {
            $assocKey = '';
        }

        // adds the distinct clause id needed
        $fieldName  = $this->_resolveFindByFieldName($field);
        $fieldQuery = ($distinct ? "DISTINCT $fieldName" : "$fieldName");

        // creates the query instance
        $q = $this->createQuery($queryAlias)
                  ->select("$fieldQuery AS $field");

        // adds the assockey if needed
        if (!empty($assocKey)) {
            $q->addSelect("$assocKey as $assocKey");
        }

        // adds the where clause if present
        if (!empty($where)) {
            $q->where($where);
        }

        // adds the orderby if present
        if (!empty($orderBy) && $this->hasField($orderBy)) {
            $q->orderBy($orderBy);
        }

        return $q;
    }

    /**
     * Select and return the max/min/sum/count value of a field.
     *
     * @param string $field  The name of the field we wish to marshall.
     * @param string $option MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param string $where  The where clause (optional) (default='').
     *
     * @return mixed The resulting min/max/sum/count value.
     */
    public function selectFieldFunction($field, $option = 'MAX', $where = '')
    {
        // creates the query instance
        $q = $this->selectFieldFunctionQuery($field, $option, $where);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return the max/min/sum/count array values of a field grouped by the associated key.
     *
     * @param string $field    The name of the field we wish to marshall.
     * @param string $option   MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param string $where    The where clause (optional) (default='').
     * @param string $assocKey The key field to use to build the associative index (optional) (default='' which defaults to the primary key).
     *
     * @return array The resulting min/max/sum/count array.
     */
    public function selectFieldFunctionArray($field, $option = 'MAX', $where = '', $assocKey = '')
    {
        // validate the associatibe index
        if (empty($assocKey) || !$this->hasField($assocKey)) {
            $assocKey = $this->getIdentifier();
        }

        // validate the option
        $option = strtoupper($option);
        if (!in_array($option, array('MIN', 'MAX', 'SUM', 'COUNT'))) {
            $option = 'MAX';
        }

        // creates the query instance
        $q = $this->selectFieldFunctionQuery($field, $option, $where, $assocKey);

        return $q->execute()->toKeyValueArray($assocKey, $option);
    }

    /**
     * Field Function Query creation.
     *
     * @param string  $field     The name of the field we wish to marshall.
     * @param string  $option    MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param string  $where     The where clause (optional) (default='').
     * @param string  $assocKey  The key field to use to build the associative index (optional) (default='' which defaults to the primary key).
     * @param boolean $distinct  Whether or not to count distinct entries (optional) (default='false').
     *
     * @return Doctrine_Query The resulting query object.
     */
    public function selectFieldFunctionQuery($field = '1', $option = 'COUNT', $where = '', $assocKey = '', $distinct = false)
    {
        $queryAlias = 'dctrn_find';

        // validate the associative index
        if (!empty($assocKey) && $this->hasField($assocKey)) {
            $queryAlias .= ($assocKey ? " INDEXBY $assocKey" : '');
        } else {
            $assocKey = '';
        }

        // validate the option
        $ucOption = strtoupper($option);
        if (!in_array($ucOption, array('MIN', 'MAX', 'SUM', 'COUNT'))) {
            $ucOption = 'COUNT';
        }

        $fieldName = $this->_resolveFindByFieldName($field);
        $distinct  = ($fieldName && $ucOption == 'COUNT' && $distinct) ? 'DISTINCT ' : '';
        if (!$fieldName) {
            if ($ucOption == 'COUNT') {
                $fieldName = '1';
            } else {
                $fieldName = $this->getIdentifier();
            }
        }

        $q = $this->createQuery($queryAlias)
                  ->select("$ucOption({$distinct}{$fieldName}) AS $option");

        // adds the assockey if needed
        if (!empty($assocKey)) {
            $q->addSelect("$assocKey as $assocKey");
            $q->addGroupBy($assocKey);
        }

        // adds the where clause if present
        if (!empty($where)) {
            $q->where($where);
        }

        return $q;
    }

    /**
     * Return a number of rows.
     *
     * @param string  $where    The where clause (optional) (default='').
     * @param string  $column   The column to place in the count phrase (optional) (default='1').
     * @param boolean $distinct Whether or not to count distinct entries (optional) (default='false').
     *
     * @return integer The resulting object count.
     */
    public function selectCount($where = '', $column = '1', $distinct = false)
    {
        // creates the query instance
        $q = $this->selectFieldFunctionQuery($column, 'COUNT', $where, '', $distinct);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select an object count by ID.
     *
     * @param integer $value         The value to match.
     * @param string  $column        The column to match the value against (optional) (default='id').
     * @param string  $transformFunc Transformation function to apply to $id (optional) (default=null).
     *
     * @return integer The resulting object count.
     * @throws Exception If id paramerter is empty or non-numeric.
     */
    public function selectCountBy($value, $column = 'id', $transformFunc = '')
    {
        if (!$value) {
            throw new Exception(__f('The parameter %s must not be empty', 'value'));
        }

        if ($column == 'id' && !is_numeric($value)) {
            throw new Exception(__f('The parameter %s must be numeric', 'value'));
        }

        // creates the query instance
        $q = $this->selectFieldFunctionQuery();

        if ($transformFunc) {
            $q->where($transformFunc.'(dctrn_find.'.$this->_resolveFindByFieldName($column).') = ?', (array)$value);
        } else {
            $q->where($this->buildFindByWhere($column), (array)$value);
        }

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return a collection.
     *
     * @param string  $where        The where clause (optional) (default='').
     * @param string  $orderBy      The order by clause (optional) (default='').
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1).
     * @param string  $assocKey     The key field to use to build the associative index (optional) (default='').
     *
     * @return Doctrine_Collection The resulting collection.
     */
    public function selectCollection($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '')
    {
        // creates the query instance
        $q = $this->selectQuery($where, $orderBy, $limitOffset, $limitNumRows, $assocKey);

        return $q->execute();
    }

    /**
     * Select Query creation.
     *
     * @param string  $where        The where clause (optional) (default='').
     * @param string  $orderBy      The order by clause (optional) (default='').
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1).
     * @param string  $assocKey     The key field to use to build the associative index (optional) (default='').
     *
     * @return Doctrine_Query The resulting query object.
     */
    public function selectQuery($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '')
    {
        $queryAlias = 'dctrn_find';

        // validate the associative index
        if (!empty($assocKey) && $this->hasField($assocKey)) {
            $queryAlias .= ($assocKey ? " INDEXBY $assocKey" : '');
        } else {
            $assocKey = '';
        }

        // creates the query instance
        $q = $this->createQuery($queryAlias);

        // adds the where clause if present
        if (!empty($where)) {
            $q->where($where);
        }

        // adds the orderby if present
        if (!empty($orderBy) && $this->hasField($orderBy)) {
            $q->orderBy($orderBy);
        }

        // adds the offset if present
        if ($limitOffset > 0) {
            $q->offset($limitOffset);
        }

        // adds the limit if present
        if ($limitNumRows > 0) {
            $q->limit($limitNumRows);
        }

        return $q;
    }

    /**
     * Increment a field by the given increment.
     *
     * @param string  $incfield The column which stores the field to increment.
     * @param integer $value    The value of the object holding the field we wish to increment.
     * @param string  $column   The column to match the value against (optional) (default='id').
     * @param integer $inccount The amount by which to increment the field (optional) (default=1).
     *
     * @return integer The result from the increment operation.
     */
    public function incrementFieldBy($incfield, $value, $column = 'id', $inccount = 1)
    {
        return $this->createQuery('dctrn_find')
                    ->update()
                    ->set("$incfield", "$incfield + $inccount")
                    ->where("$column = ?", array($value))
                    ->execute();
    }

    /**
     * Decrement a field by the given decrement.
     *
     * @param string  $decfield The column which stores the field to decrement.
     * @param integer $value    The value of the object holding the field we wish to increment.
     * @param string  $column   The column to match the value against (optional) (default='id').
     * @param integer $deccount The amount by which to decrement the field (optional) (default=1).
     *
     * @return integer The result from the decrement operation.
     */
    public function decrementFieldBy($decfield, $value, $column = 'id', $deccount = 1)
    {
        return $this->incrementFieldBy($decfield, $value, $column, 0 - $deccount);
    }
}
