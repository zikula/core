<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    private $fields = [];

    /**
     * Enabled operators.
     *
     * @var array
     */
    private $ops = [];

    /**
     * Constructor.
     *
     * @param array $fields  Set of fields to use, see setFields() (optional) (default=null).
     * @param array $ops     Operators to enable, see activateOperators() (optional) (default=null).
     * @param bool  $default set the plugin to default (optional) (default=false).
     */
    public function __construct($fields = null, array $ops = [], $default = false)
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
        } elseif (!empty($fields) && array_search($fields, $this->fields) === false) {
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

        $ops = [];
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
