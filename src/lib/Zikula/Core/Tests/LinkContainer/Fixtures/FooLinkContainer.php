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

class FooLinkContainer implements LinkContainerInterface
{
    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_USER === $type) {
            return $this->getUser();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }

        return [];
    }

    private function getAdmin(): array
    {
        $links = [];
        $links[] = [
            'url' => '/foo/admin',
            'text' => 'Foo Admin',
            'icon' => 'wrench'
        ];

        return $links;
    }

    private function getUser(): array
    {
        $links = [];
        $links[] = [
            'url' => '/foo',
            'text' => 'Foo',
            'icon' => 'check-square-o'
        ];

        return $links;
    }

    private function getAccount(): array
    {
        $links = [];
        $links[] = [
            'url' => '/foo/account',
            'text' => 'Foo Account',
            'icon' => 'wrench'
        ];

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaFooExtension';
    }
}
