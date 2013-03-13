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
 *   <id>{id}</id>
 *
 * @return       string the atom ID
 */
function smarty_function_id($params, &$smarty)
{
    $baseurl = System::getBaseUrl();

    $parts = parse_url($baseurl);

    $starttimestamp = strtotime(System::getVar('startdate'));
    $startdate = strftime('%Y-%m-%d', $starttimestamp);

    $sitename = System::getVar('sitename');
    $sitename = preg_replace('/[^a-zA-Z0-9-\s]/', '', $sitename);
    $sitename = DataUtil::formatForURL($sitename);

    return "tag:{$parts['host']},{$startdate}:{$sitename}";
}
