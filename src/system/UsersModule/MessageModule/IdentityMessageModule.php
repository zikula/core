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

class IdentityMessageModule implements MessageModuleInterface
{
    /**
* @inheritDoc
     */
    public function getInboxUrl($uid = null)
    {
        return '#';
    }

    /**
* @inheritDoc
     */
    public function getMessageCount($uid = null, $unreadOnly = false)
    {
        return 0;
    }

    /**
* @inheritDoc
     */
    public function getSendMessageUrl($uid = null)
    {
        return '#';
    }
}
