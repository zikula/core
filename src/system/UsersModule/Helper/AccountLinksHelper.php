<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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

    public function __construct(LinkContainerCollector $collector)
    {
        $this->collector = $collector;
    }

    public function getAllAccountLinks(): array
    {
        // get the account links provided by any modules
        $accountLinksPerModule = $this->collector->getAllLinksByType(LinkContainerInterface::TYPE_ACCOUNT);
        $accountLinks = [];
        foreach ($accountLinksPerModule as $moduleName => $links) {
            foreach ($links as $link) {
                $accountLinks[] = [
                    'module' => $moduleName,
                    'url' => $link['url'],
                    'text' => $link['text'],
                    'icon' => $link['icon']
                ];
            }
        }

        return $accountLinks;
    }
}
