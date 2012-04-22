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
 * SimplePieFeed class.
 */
class SimplePieFeed extends SimplePie
{
    /**
     * Class constructor.
     *
     * @param string  $feed_url       The URL to the feed (optional).
     * @param integer $cache_duration The duration (in seconds) that the feed contents will be retained in cache.
     */
    public function __construct($feed_url = null, $cache_duration = null, $cache_dir = null)
    {
        parent::__construct();
        if (isset($cache_dir)) { 
            $this->set_cache_location($cache_dir);
        } else {
            $this->set_cache_location(CacheUtil::getLocalDir('feeds'));
        }
        if (isset($cache_duration)) {
            $this->set_cache_duration($cache_duration);
        }
        if (isset($feed_url)) {
            $this->set_feed_url($feed_url);
        }
    }
}

if (System::isLegacyMode()) {
    class ZFeeds extends SimplePieFeed {}
}