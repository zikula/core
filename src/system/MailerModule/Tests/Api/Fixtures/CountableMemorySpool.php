<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Tests\Api\Fixtures;

final class CountableMemorySpool extends \Swift_MemorySpool implements \Countable
{
    /**
     * @return int
     */
    public function count()
    {
        return count($this->messages);
    }

    /**
     * @return \Swift_Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
