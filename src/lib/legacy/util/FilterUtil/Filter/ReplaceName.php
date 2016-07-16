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
 * Simple field name replacement.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
class FilterUtil_Filter_ReplaceName extends FilterUtil_AbstractPlugin implements FilterUtil_ReplaceInterface
{
    /**
     * Replace pairs.
     *
     * Form is old => new.
     *
     * @var array
     */
    protected $pair = [];

    /**
     * Constructor.
     *
     * Argument $config may contain:
     *  pair: array of replace pairs in form old => new.
     *
     * @param array $config Configuration
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['pair']) && is_array($config['pair'])) {
            $this->addPair($config['pair']);
        }
    }

    /**
     * Add new replace pair (fieldname => replace with).
     *
     * @param mixed $pair Replace pair
     *
     * @return void
     */
    public function addPair($pair)
    {
        foreach ($pair as $f => $r) {
            if (is_array($r)) {
                $this->addPair($r);
            } else {
                $this->pair[$f] = $r;
            }
        }
    }

    /**
     * Replace field's value.
     *
     * @param string $field Field name
     * @param string $op    Filter operator
     * @param string $value Filter value
     *
     * @return array New filter set
     */
    public function replace($field, $op, $value)
    {
        if (isset($this->pair[$field]) && !empty($this->pair[$field])) {
            $field = $this->pair[$field];
        }

        return [$field, $op, $value];
    }
}
