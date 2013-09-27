<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * FilterUtil replace interface
 */
interface FilterUtil_ReplaceInterface
{
    /**
     * Replace whatever the plugin has to replace.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return array ($field, $op, $value)
     */
    public function replace($field, $op, $value);
}
