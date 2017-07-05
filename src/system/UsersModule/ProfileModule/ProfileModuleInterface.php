<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\ProfileModule;

/**
 * Interface ProfileModuleInterface
 */
interface ProfileModuleInterface
{
    /**
     * Display a module-defined user display name (e.g. set by the user) or display the uname as defined by the UserModule
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param int|string $uid The user's id or name
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getDisplayName($uid = null);

    /**
     * Get the url to a user's profile.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param int|string $uid The user's id or name
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getProfileUrl($uid = null);

    /**
     * Get the avatar image for a given user.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param int|string $uid The user's id or name
     * @param array $parameters
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getAvatar($uid = null, array $parameters = []);
}
