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

namespace Zikula\Core\Tests\LinkContainer\Fixtures;

use Zikula\Core\LinkContainer\LinkContainerInterface;

class BarLinkContainer implements LinkContainerInterface
{
    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if ('bar' === $type) {
            return $this->getBar();
        }
        if (LinkContainerInterface::TYPE_USER === $type) {
            return $this->getUser();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }

        return [];
    }

    private function getBar(): array
    {
        $links = [];
        $links[] = [
            'url' => '/bar/admin',
            'text' => 'Bar Admin',
            'icon' => 'plus'
        ];

        return $links;
    }

    private function getUser(): array
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

    private function getAccount(): array
    {
        $links = [];
        $links[] = [
            'url' => '/bar/account',
            'text' => 'Bar Account',
            'icon' => 'check'
        ];

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaBarExtension';
    }
}
