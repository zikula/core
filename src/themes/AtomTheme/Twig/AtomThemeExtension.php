<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package SpecTheme
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AtomTheme\Twig;

class AtomThemeExtension extends \Twig_Extension
{
    public function __construct()
    {
    }

    public function getName()
    {
        return 'atomtheme_extension';
    }

    /**
     * Register provided functions.
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('atomId', [$this, 'id']),
            new \Twig_SimpleFunction('atomFeedLastUpdated', [$this, 'atomFeedLastUpdated']),
        );
    }

    public function id()
    {
        $baseurl = \System::getBaseUrl();

        $parts = parse_url($baseurl);

        $starttimestamp = strtotime(\System::getVar('startdate'));
        $startdate = strftime('%Y-%m-%d', $starttimestamp);

        $sitename = \System::getVar('sitename');
        $sitename = preg_replace('/[^a-zA-Z0-9-\s]/', '', $sitename);
        $sitename = \DataUtil::formatForURL($sitename);

        return "tag:{$parts['host']},{$startdate}:{$sitename}";
    }

    public function atomFeedLastUpdated()
    {
        if (!isset($GLOBALS['atom_feed_lastupdated'])) {
            $GLOBALS['atom_feed_lastupdated'] = time();
        }

        return strftime('%Y-%m-%dT%H:%M:%SZ', $GLOBALS['atom_feed_lastupdated']);
    }
}
