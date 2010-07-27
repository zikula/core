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
 * ZFeed.
 */
class ZFeed extends SimplePie
{
    /**
     * Class constructor
     *
     * @param string  $feed_url       The URL to the feed (optional).
     * @param integer $cache_duration The duration (in seconds) that the feed contents will be retained in cache.
     */
    public function __construct($feed_url = null, $cache_duration = null)
    {
        $cache_dir = CacheUtil::getLocalDir() . '/feeds';
        $this->SimplePie($feed_url, $cache_dir, $cache_duration);
    }
}