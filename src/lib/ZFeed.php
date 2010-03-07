<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Mark West
 * @package Zikula_Core
 */

//TODO A [add this to autoloader] drak
require_once 'lib/vendor/SimplePie/simplepie.inc';

/**
 * ZFeed
 *
 * @package Zikula_Core
 * @subpackage Feed
 */
class ZFeed extends SimplePie
{
    /**
     * Class constructor
     */
    function ZFeed($feed_url = null, $cache_duration = null)
    {
        $cache_dir = CacheUtil::getLocalDir() . '/feeds';
        $this->SimplePie($feed_url, $cache_dir, $cache_duration);
    }
}
