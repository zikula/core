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

namespace Zikula\MailerModule\Api\ApiInterface;

use Swift_Message;

interface MailerApiInterface
{
    /**
     * API function to send e-mail message.
     * It is assumed that basic parameters for sender and recipient(s) have already been set.
     *
     * @param Swift_Message $message The message object
     * @param string        $subject message subject
     * @param string        $body message body, if altbody is provided then
     *                            this is the HTML version of the body
     * @param string        $altBody alternative plain-text message body, if specified the
     *                               e-mail will be sent as multipart/alternative
     * @param bool          $html HTML flag, if altbody is not specified then this
     *                            indicates whether body contains HTML or not; if altbody is
     *                            specified, then this value is ignored, the body is assumed
     *                            to be HTML, and the altbody is assumed to be plain text
     * @param array         $headers custom headers to add - an array ['header' => 'content', 'header' => 'content']
     * @param array         $attachments array of either absolute filenames to attach
     *                                   to the mail or array of arrays in format
     *                                   [$path, $filename, $encoding, $type]
     * @param array         $stringAttachments array of arrays to treat as attachments, format [$string, $filename, $encoding, $type]
     * @param array         $embeddedImages array of absolute filenames to image files to embed in the mail
     *
     * @throws \RuntimeException Thrown if there's an error sending the e-mail message
     *
     * @return bool true if successful
     */
    public function sendMessage(Swift_Message $message, $subject = null, $body = null, $altBody = '', $html = false, array $headers = [], array $attachments = [], array $stringAttachments = [], array $embeddedImages = []);
}
