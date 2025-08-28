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

class BarExtensionMenu implements ExtensionMenuInterface
{
    public function get(string $type = ExtensionMenuInterface::CONTEXT_ADMIN): iterable
    {
        $method = 'get' . ucfirst($type);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return [];
    }

    private function getUser(): iterable
    {
        yield MenuItem::linkToUrl('Visit admin area', null, '/admin/');
        yield MenuItem::linkToUrl('Search in Google', 'fab fa-google', 'https://google.com');
    }

    private function getBar(): iterable
    {
        yield MenuItem::linkToUrl('Visit public website', null, '/');
    }

    private function getAccount(): iterable
    {
        yield MenuItem::linkToUrl('Visit public website', null, '/');
        yield MenuItem::linkToUrl('Search in Google', 'fab fa-google', 'https://google.com');
    }

    public function getBundleName(): string
    {
        return 'ZikulaBarExtension';
    }
}
