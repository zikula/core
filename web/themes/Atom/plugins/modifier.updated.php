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
 * Smarty modifier format an issue date for an atom news feed.
 *
 * Example
 *
 *   {$MyVar|updated}
 *
 * @param array $string The contents to transform.
 *
 * @return       string   the updated output
 */
function smarty_modifier_updated($string)
{
    global $atom_feed_lastupdated;

    $timestamp = strtotime($string);

    if (!isset($atom_feed_lastupdated) || $timestamp > $atom_feed_lastupdated) {
        $atom_feed_lastupdated = $timestamp;
    }

    return strftime('%Y-%m-%dT%H:%M:%SZ', $timestamp);
}
