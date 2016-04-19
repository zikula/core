<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\MailerModule\Api;

/**
 * API functions used by user controllers
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * API function to send e-mail message
     *
     * @param mixed[] $args {
     *       @type string       $fromname name of the sender
     *       @type string       $fromaddress address of the sender
     *       @type string       $toname name to the recipient
     *       @type string       $toaddress the address of the recipient
     *       @type string       $replytoname name to reply to
     *       @type string       $replytoaddress address to reply to
     *       @type string       $subject message subject
     *       @type string       $contenttype optional contenttype of the mail (default config)
     *       @type string       $charset optional charset of the mail (default config)
     *       @type string       $encoding optional mail encoding (default config)
     *       @type string       $body message body, if altbody is provided then
     *                                    this is the HTML version of the body
     *       @type string       $altbody alternative plain-text message body, if specified the
     *                                    e-mail will be sent as multipart/alternative
     *       @type array        $cc addresses to add to the cc list
     *       @type array        $bcc addresses to add to the bcc list
     *       @type array|string $headers custom headers to add
     *       @type int          $html HTML flag, if altbody is not specified then this
     *                                    indicates whether body contains HTML or not; if altbody is
     *                                    specified, then this value is ignored, the body is assumed
     *                                    to be HTML, and the altbody is assumed to be plain text
     *       @type array        $attachments array of either absolute filenames to attach
     *                                    to the mail or array of arrays in format
     *                                    array($path,$filename,$encoding,$type)
     *       @type array        $stringattachments array of arrays to treat as attachments, format array($string,$filename,$encoding,$type)
     *       @type array        $embeddedimages array of absolute filenames to image files to embed in the mail
     *                       }
     *
     * @throws \RuntimeException Thrown if there's an error sending the e-mail message
     *
     * @return bool true if successful
     */
    public function sendmessage($args)
    {
        $mailer = $this->getContainer()->get('zikula_mailer_module.api.mailer');

        return $mailer->sendMessage($args);
    }
}
