<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\MessageModule;

/**
 * Interface MessageModuleInterface
 */
interface MessageModuleInterface
{
    /**
     * Get the url to a uid's inbox.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getInboxUrl($uid = null);

    /**
     * Get the count of all or only unread messages owned by the uid.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @param bool $unreadOnly
     * @return int
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getMessageCount($uid = null, $unreadOnly = false);

    /**
     * Get the url to send a message to the identified uid.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getSendMessageUrl($uid = null);
}
