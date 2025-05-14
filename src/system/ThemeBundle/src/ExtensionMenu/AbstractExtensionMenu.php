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

namespace Zikula\ThemeBundle\ExtensionMenu;

abstract class AbstractExtensionMenu implements ExtensionMenuInterface
{
    public function get(string $context = ExtensionMenuInterface::CONTEXT_ADMIN): iterable
    {
        if (ExtensionMenuInterface::CONTEXT_ADMIN === $context) {
            return $this->getAdmin();
        }
        if (ExtensionMenuInterface::CONTEXT_USER === $context) {
            return $this->getUser();
        }
        if (ExtensionMenuInterface::CONTEXT_ACCOUNT === $context) {
            return $this->getAccount();
        }

        return [];
    }

    protected function getAdmin(): iterable
    {
        return [];
    }

    protected function getUser(): iterable
    {
        return [];
    }

    protected function getAccount(): iterable
    {
        return [];
    }
}
