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

namespace Zikula\UsersModule\ProfileModule;

use InvalidArgumentException;

/**
 * Interface ProfileModuleInterface
 */
interface ProfileModuleInterface
{
    /**
     * Display a module-defined user display name (e.g. set by the user) or display the uname as defined by the UserModule
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     *
     * @param int|string $userId The user's id or name
     * @throws InvalidArgumentException if provided $userId is not null and invalid
     */
    public function getDisplayName($userId = null): string;

    /**
     * Get the url to a user's profile.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     *
     * @param int|string $userId The user's id or name
     * @throws InvalidArgumentException if provided $userId is not null and invalid
     */
    public function getProfileUrl($userId = null): string;

    /**
     * Get the avatar image for a given user.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     *
     * @param int|string $userId The user's id or name
     * @throws InvalidArgumentException if provided $userId is not null and invalid
     */
    public function getAvatar($userId = null, array $parameters = []): string;

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;
}
