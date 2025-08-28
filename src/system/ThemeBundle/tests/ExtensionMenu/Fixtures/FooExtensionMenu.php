<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeBundle\Tests\ExtensionMenu\Fixtures;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

class FooExtensionMenu implements ExtensionMenuInterface
{
    public function get(string $type = ExtensionMenuInterface::CONTEXT_ADMIN): iterable
    {
        if (ExtensionMenuInterface::CONTEXT_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (ExtensionMenuInterface::CONTEXT_USER === $type) {
            return $this->getUser();
        }

        return [];
    }

    private function getAdmin(): iterable
    {
        yield MenuItem::linkToUrl('Visit public website', null, '/');
        yield MenuItem::linkToUrl('Search in Google', 'fab fa-google', 'https://google.com');
        yield MenuItem::linkToUrl('Another url', null, 'https://google.de');
    }

    private function getUser(): iterable
    {
        yield MenuItem::linkToUrl('Visit admin area', null, '/admin/');
    }

    public function getBundleName(): string
    {
        return 'ZikulaFooExtension';
    }
}
