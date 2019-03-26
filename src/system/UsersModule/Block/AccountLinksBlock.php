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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\UsersModule\Helper\AccountLinksHelper;

class AccountLinksBlock extends AbstractBlockHandler
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var AccountLinksHelper
     */
    private $accountLinksHelper;

    /**
     * @param array $properties
     * @return string
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('Accountlinks::', $properties['title'] . "::", ACCESS_READ)) {
            return '';
        }

        if (!$this->kernel->isBundle('ZikulaUsersModule')) {
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

    /**
     * @required
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function setKernel(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @required
     * @param AccountLinksHelper $accountLinksHelper
     */
    public function setAccountLinksHelper(AccountLinksHelper $accountLinksHelper)
    {
        $this->accountLinksHelper = $accountLinksHelper;
    }
}
