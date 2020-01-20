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

namespace Zikula\MenuModule\ExtensionMenu;

use Knp\Menu\ItemInterface;

interface ExtensionMenuInterface
{
    public const TYPE_ADMIN = 'admin';

    public const TYPE_USER = 'user';

    public const TYPE_ACCOUNT = 'account';

    public function getBundleName(): string;

    /**
     * get a Menu for the type requested (admin|user|account)
     * return null if Menu of that type is not available
     */
    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface;
}
