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
 * Filter entries using a m:n relationship.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
class FilterUtil_Filter_Mnlist extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $ops = [];

    /**
     * Field configuration.
     *
     * An array of fields in the relation table in the form name => field.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * The table names of the relation tables.
     *
     * In the form name => table.
     *
     * @var array
     */
    protected $mndbtable = [];

    /**
     * The table names in database.
     *
     * In the form name => table.
     *
     * @var aray
     */
    protected $mntable = [];

    /**
     * The column set of the relation tables.
     *
     * In the form name => columns.
     *
     * @var array
     */
    protected $mncolumn = [];

    /**
     * The field in table to compare the relationed field with.
     *
     * In the form name => field.
     *
     * @var array
     */
    protected $comparefield = [];

    /**
     * Constructor
     *
     * Argument $config may contain "fields". This is an array in the form
     * name => [field => '', table => '', comparefield => ''].
     * name is the filter field name.
     * field is the id field in the mn-relationship table.
     * table is the table of the mn-relationship.
     * comparefield is the field to compare with in the table.
     *
     * @param array $config Plugin configuration
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
     * @return array Operators
     */
    public function availableOperators()
    {
        return ['eq', 'ne'];
    }

    /**
     * Adds fields to list in common way.
     *
     * Takes an array in the form
     * name => [field => '', table => '', comparefield => ''].
     * name is the filter field name.
     * field is the id field in the mn-relationship table.
     * table is the table of the mn-relationship.
     * comparefield is the field to compare with in the table.
     *
     * @param mixed $fields Fields to add
     *
     * @return void
     */
    public function addFields($fields)
    {
        foreach ($fields as $f => $r) {
            $this->fields[$f] = $r['field'];
            $this->setListTable($f, $r['table']);
            $this->setCompareField($f, $r['comparefield']);
        }
    }

    /**
     * Activates the requested Operators.
     *
     * @param mixed $op Operators to activate
     *
     * @return void
     */
    public function activateOperators($op)
    {
        static $ops = ['eq', 'ne'];

        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $ops) !== false) {
            $this->ops[] = $op;
        }
    }

    /**
     * Returns the fields.
     *
     * @return array List of fields
     */
    public function getFields()
    {
        return array_keys($this->fields);
    }

    /**
     * Get activated operators.
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
     * Set the n:m-Table.
     *
     * @param string $name  Filter field name
     * @param string $table Table name
     *
     * @return void
     */
    public function setListTable($name, $table)
    {
        $this->mndbtable[$name] = $table;
        $dbtable = &DBUtil::getTables();
        $this->mntable[$name] = $dbtable[$table];
        $this->mncolumn[$name] = $dbtable[$table . '_column'];
    }

    /**
     * Set the Compare field.
     *
     * @param string $name  Filter field name
     * @param string $field Field name
     *
     * @return void
     */
    public function setCompareField($name, $field)
    {
        if (isset($this->mncolumn[$name][$field]) && $this->fieldExists($field)) {
            $this->comparefield[$name] = $field;
        }
    }

    /**
     * Returns SQL code.
     *
     * @param string $field Field name
     * @param string $op    Operator
     * @param string $value Test value
     *
     * @return array SQL code array
     */
    public function getSQL($field, $op, $value)
    {
        if (!isset($this->fields[$field])) {
            return '';
        }

        $where = '';
        $alias = 'plg' . $this->id . $field;

        switch ($op) {
            case 'ne':
                $where = $value.' NOT IN ('.
                         'SELECT '.$this->mncolumn[$field][$this->fields[$field]].' FROM '.$this->mntable[$field].' '.$alias.
                         ' WHERE '.$this->column[$this->comparefield[$field]]." = $alias.".$this->mncolumn[$field][$this->comparefield[$field]].')';

                break;

            case 'eq':
                $where = $value.' IN ('.
                         'SELECT '.$this->mncolumn[$field][$this->fields[$field]].' FROM '.$this->mntable[$field].' '.$alias.
                         ' WHERE '.$this->column[$this->comparefield[$field]]." = $alias.".$this->mncolumn[$field][$this->comparefield[$field]].')';

                break;
        }

        return ['where' => $where];
    }
}
