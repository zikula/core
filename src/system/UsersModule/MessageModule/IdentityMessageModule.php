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

namespace Zikula\UsersModule\MessageModule;

class IdentityMessageModule implements MessageModuleInterface
{
    public function getInboxUrl($userId = null): string
    {
        return '#';
    }

    public function getMessageCount($userId = null, bool $unreadOnly = false): int
    {
        return 0;
    }

    public function getSendMessageUrl($userId = null): string
    {
        return '#';
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersModule';
    }
}
