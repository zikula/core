<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Tests\LinkContainer\Fixtures;

use Zikula\Core\LinkContainer\LinkContainerInterface;

class FooLinkContainer implements LinkContainerInterface
{
    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        if ('bar' == $type) {
            return $this->getBar();
        }
        if (LinkContainerInterface::TYPE_USER == $type) {
            return $this->getUser();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT == $type) {
            return $this->getAccount();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];
        $links[] = [
            'url' => '/foo/admin',
            'text' => 'Foo Admin',
            'icon' => 'wrench'
        ];

        return $links;
    }

    /**
     * get the User Links for this extension
     *
     * @return array
     */
    private function getUser()
    {
        $links = [];
        $links[] = [
            'url' => '/foo',
            'text' => 'Foo',
            'icon' => 'check-square-o'
        ];

        return $links;
    }

    /**
     * get the Account Links for this extension
     *
     * @return array
     */
    private function getAccount()
    {
        $links = [];
        $links[] = [
            'url' => '/foo/account',
            'text' => 'Foo Account',
            'icon' => 'wrench'
        ];

        return $links;
    }

    /**
     * set the BundleName as required buy the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaFooExtension';
    }
}
