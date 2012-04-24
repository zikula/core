<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package FilterUtil
 * @subpackage Filter
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Simple field name replacement.
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
    protected $pair = array();

    /**
     * Constructor.
     *
     * Argument $config may contain:
     *  pair: array of replace pairs in form old => new.
     *
     * @param array $config Configuration.
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
     * @param mixed $pair Replace pair.
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
     * @param string $field Field name.
     * @param string $op    Filter operator.
     * @param string $value Filter value.
     *
     * @return array New filter set.
     */
    public function replace($field, $op, $value)
    {
        if (isset($this->pair[$field]) && !empty($this->pair[$field])) {
            $field = $this->pair[$field];
        }

        return array($field, $op, $value);
    }
}
