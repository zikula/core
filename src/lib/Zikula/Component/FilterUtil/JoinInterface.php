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

namespace Zikula\Component\FilterUtil;

/**
 * FilterUtil replace interface
 */
interface JoinInterface
{
    /**
     * add Join to QueryBuilder.
     */
    public function addJoinsToQuery();
}
