<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

interface Zikula_Collection_Interface extends ArrayAccess, Countable, IteratorAggregate
{
    function add($value);
    function set($key, $value);
    function get($key);
    function del($key);
    function has($key);
    function hasCollection();
    function getCollection();
}
