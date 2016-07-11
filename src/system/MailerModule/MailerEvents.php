<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule;

/**
 * Class MailerEvents
 * Contains constant values for mailer event names.
 * The subject of each event is the instance of Swift_Message
 */
class MailerEvents
{
    /**
     * Occurs when a new message should be sent.
     */
    const SEND_MESSAGE_START = 'module.mailer.api.sendmessage';

    /**
     * Occurs right before a message is sent.
     */
    const SEND_MESSAGE_PERFORM = 'module.mailer.api.perform';

    /**
     * Occurs after a message has been sent successfully.
     */
    const SEND_MESSAGE_SUCCESS = 'module.mailer.api.success';

    /**
     * Occurs when a message could not be sent.
     */
    const SEND_MESSAGE_FAILURE = 'module.mailer.api.failure';
}
