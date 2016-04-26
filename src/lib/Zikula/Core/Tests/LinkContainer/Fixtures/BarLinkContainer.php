<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Tests\LinkContainer\Fixtures;

use Zikula\Core\LinkContainer\LinkContainerInterface;

class BarLinkContainer implements LinkContainerInterface
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
        $method = 'get' . ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getBar()
    {
        $links = [];
        $links[] = [
            'url' => '/bar/admin',
            'text' => 'Bar Admin',
            'icon' => 'plus'
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
            'url' => '/bar',
            'text' => 'Bar',
            'icon' => 'check'
        ];
        $links[] = [
            'url' => '/bar2',
            'text' => 'Bar 2',
            'icon' => 'check'
        ];

        return $links;
    }

    /**
     * get the Account links for this extension
     *
     * @return array
     */
    private function getAccount()
    {
        $links = [];
        $links[] = [
            'url' => '/bar/account',
            'text' => 'Bar Account',
            'icon' => 'check'
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
        return 'ZikulaBarExtension';
    }
}
