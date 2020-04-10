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

use InvalidArgumentException;

/**
 * Interface MessageModuleInterface
 */
interface MessageModuleInterface
{
    /**
     * Get the url to a user's inbox.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     *
     * @param int|string $userId The user's id or name
     * @throws InvalidArgumentException if provided $userId is not null and invalid
     */
    public function getInboxUrl($userId = null): string;

    /**
     * Get the count of all or only unread messages owned by the uid.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     *
     * @param int|string $userId The user's id or name
     * @throws InvalidArgumentException if provided $userId is not null and invalid
     */
    public function getMessageCount($userId = null, bool $unreadOnly = false): int;

    /**
     * Get the url to send a message to the identified uid.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     *
     * @param int|string $userId The user's id or name
     * @throws InvalidArgumentException if provided $userId is not null and invalid
     */
    public function getSendMessageUrl($userId = null): string;

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;
}
