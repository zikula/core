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

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ExtensionMenuInterface
{
    public const CONTEXT_ADMIN = 'admin';
    public const CONTEXT_USER = 'user';
    public const CONTEXT_ACCOUNT = 'account';

    public function getBundleName(): string;

    /**
     * get a Menu for the type requested (admin|user|account)
     *
     * @return MenuItemInterface[]
     */
    public function get(string $context = self::CONTEXT_ADMIN): iterable;
}
