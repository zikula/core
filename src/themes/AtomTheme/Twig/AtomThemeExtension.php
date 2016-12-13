<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AtomTheme\Twig;

class AtomThemeExtension extends \Twig_Extension
{
    public function __construct()
    {
    }

    /**
     * Register provided functions.
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('atomId', [$this, 'id']),
            new \Twig_SimpleFunction('atomFeedLastUpdated', [$this, 'atomFeedLastUpdated']),
        ];
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
