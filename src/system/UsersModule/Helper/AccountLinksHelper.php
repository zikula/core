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
        // get the menu links for modules
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

        return $legacyAccountLinksFromNew;
    }
}
