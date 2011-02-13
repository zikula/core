<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package XXXX
 * @subpackage XXXX
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Token_Storage class.
 */
interface Zikula_Token_Storage
{
    public function get($id);
    public function save($id, $token, $timestamp);
    public function delete($id);
}
