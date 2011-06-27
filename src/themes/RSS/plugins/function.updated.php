<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to generate a valid atom ID for the feed
 *
 * Example
 *
 *   <updated>{updated}</updated>
 *
 * @return       string the atom ID
 */
function smarty_function_updated($params, &$smarty)
{
    return date('D, d M Y H:i:s O', $GLOBALS['rss_feed_lastupdated']);
}
