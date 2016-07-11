<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Zikula\Core\LinkContainer\LinkContainerCollector;
use Zikula\Core\LinkContainer\LinkContainerInterface;

class AccountLinksHelper
{
    /**
     * @var LinkContainerCollector
     */
    private $collector;

    /**
     * AccountLinksHelper constructor.
     * @param LinkContainerCollector $collector
     */
    public function __construct(LinkContainerCollector $collector)
    {
        $this->collector = $collector;
    }

    public function getAllAccountLinks()
    {
        // get the menu links for Core-2.0 modules
        $accountLinks = $this->collector->getAllLinksByType(LinkContainerInterface::TYPE_ACCOUNT);
        $legacyAccountLinksFromNew = [];
        foreach ($accountLinks as $moduleName => $links) {
            foreach ($links as $link) {
                $legacyAccountLinksFromNew[] = [
                    'module' => $moduleName,
                    'url' => $link['url'],
                    'text' => $link['text'],
                    'icon' => $link['icon']
                ];
            }
        }

        // @deprecated The API function is called for old-style modules
        $legacyAccountLinks = \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'accountLinks');
        if (false === $legacyAccountLinks) {
            $legacyAccountLinks = [];
        } else {
            foreach ($legacyAccountLinks as $key => $legacyAccountLink) {
                $legacyAccountLinks[$key]['text'] = $legacyAccountLink['title'];
            }
        }

        // add the arrays together
        return $legacyAccountLinksFromNew + $legacyAccountLinks;
    }
}
