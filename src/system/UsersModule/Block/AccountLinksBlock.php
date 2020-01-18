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

namespace Zikula\UsersModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
//use Zikula\UsersModule\Helper\AccountLinksHelper;

class AccountLinksBlock extends AbstractBlockHandler
{
    /**
     * @var AccountLinksHelper
     */
    private $accountLinksHelper;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('Accountlinks::', $properties['title'] . '::', ACCESS_READ)) {
            return '';
        }

        $accountLinks = $this->accountLinksHelper->getAllAccountLinks();
        if (empty($accountLinks)) {
            return '';
        }

        return $this->renderView('@ZikulaUsersModule/Block/accountLinks.html.twig', [
            'accountLinks' => $accountLinks
        ]);
    }

//    /**
//     */
//    public function setAccountLinksHelper(AccountLinksHelper $accountLinksHelper): void
//    {
//        $this->accountLinksHelper = $accountLinksHelper;
//    }
}
