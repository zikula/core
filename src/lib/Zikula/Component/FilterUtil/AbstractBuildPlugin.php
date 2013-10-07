<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula\Component\FilterUtil
 *
 *          Please see the NOTICE file distributed with this source code for further
 *          information regarding copyright and licensing.
 */
namespace Zikula\Component\FilterUtil;

/**
 * Base class of all FilterUtil plugins.
 */
abstract class AbstractBuildPlugin extends AbstractPlugin implements BuildInterface
{
    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    private $fields = array();

    /**
     * Enabled operators.
     *
     * @var array
     */
    private $ops = array();

    /**
     * Constructor.
     *
     * @param array $fields  Set of fields to use, see setFields() (optional) (default=null).
     * @param array $ops     Operators to enable, see activateOperators() (optional) (default=null).
     * @param bool  $default set the plugin to default (optional) (default=false).
     */
    public function __construct($fields = null, array $ops = array(), $default = false)
    {
        $this->addFields($fields);
        $this->activateOperators($ops ? $ops : $this->availableOperators());
        $this->setDefault($default);
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
        } else if (!empty($fields) && array_search($fields, $this->fields) === false) {
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
     * Activates the requested Operators.
     *
     * @param mixed $op Operators to activate.
     *
     * @return void
     */
    public function activateOperators($op)
    {
        $ops = $this->availableOperators();
        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op)
            && array_search($op, $this->ops) === false
            && array_search($op, $ops) !== false
        ) {
            $this->ops[] = $op;
        }
    }

    /**
     * Get activated operators.
     *
     * @return array Set of Operators and Arrays.
     */
    public function getOperators()
    {
        $fields = $this->getFields();
        if ($this->isDefault()) {
            $fields[] = '-';
        }

        $ops = array();
        foreach ($this->ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    abstract protected function availableOperators();
}
