<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil\Plugin;

use Zikula\Component\FilterUtil;

/**
 * Simple field name replacement.
 */
class ReplaceNamePlugin extends FilterUtil\AbstractPlugin implements FilterUtil\ReplaceInterface
{
    /**
     * Replace pairs.
     *
     * Form is old => new.
     *
     * @var array
     */
    private $pair = [];

    /**
     * Constructor.
     *
     * pair: array of replace pairs in form old => new.
     *
     * @param array $pairs
     */
    public function __construct($pairs = [])
    {
        $this->addPair($pairs);
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
