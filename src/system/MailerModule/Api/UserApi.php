<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Api;

use Swift_Message;
use Zikula\ExtensionsModule\Api\VariableApi;
use ZLanguage;

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
        $dumper = $this->getContainer()->get('zikula.dynamic_config_dumper');
        $params = $dumper->getConfiguration('swiftmailer');

        // Development mailer mode
        if ($params['transport'] == 'test') {
            $output = '<p>';
            foreach ($args as $key => $value) {
                if ($key == 'password') {
                    // do not expose the password (#2149)
                    continue;
                }
                $output .= '<strong>' . $key . '</strong>: ' . $value . '<br />';
            }
            $output .= '</p>';

            $this->getContainer()->get('session')->getFlashBag()->add('status', $output);

            return true;
        }

        $mailer = $this->getContainer()->get('zikula_mailer_module.api.mailer');
        $variableApi = $this->getContainer()->get('zikula_extensions_module.api.variable');

        $sitename = $variableApi->get(VariableApi::CONFIG, 'sitename_' . ZLanguage::getLanguageCode(), $variableApi->get(VariableApi::CONFIG, 'sitename_en'));
        $adminMail = $variableApi->get(VariableApi::CONFIG, 'adminmail');

        // create new message instance
        /** @var Swift_Message */
        $message = Swift_Message::newInstance();

        // set sender details
        $fromName = (isset($args['fromname']) && $args['fromname']) ? $args['fromname'] : $sitename;
        $fromAddress = (isset($args['fromaddress'])) ? $args['fromaddress'] : $adminMail;
        $message->setFrom([$fromAddress => $fromName]);

        // add any to addresses
        if (is_array($args['toaddress'])) {
            $toAdds = [];
            foreach ($args['toaddress'] as $key => $address) {
                $toAdds[] = isset($args['toname'][$key]) ? [$address => $args['toname'][$key]] : $address;
            }
            $message->setTo($toAdds);
        } else {
            $message->setTo(isset($args['toname']) ? [$args['toaddress'] => $args['toname']] : $args['toaddress']);
        }

        // if replytoname and replytoaddress have been provided use them else use the fromname and fromaddress built earlier
        $args['replytoname'] = (!isset($args['replytoname']) || empty($args['replytoname'])) ? $fromname : $args['replytoname'];
        $args['replytoaddress'] = (!isset($args['replytoaddress'])  || empty($args['replytoaddress'])) ? $fromaddress : $args['replytoaddress'];
        $message->setReplyTo([$args['replytoaddress'] => $args['replytoname']]);

        // add any cc addresses
        if (isset($args['cc']) && is_array($args['cc'])) {
            foreach ($args['cc'] as $email) {
                if (isset($email['name'])) {
                    $message->addCc([$email['address'] => $email['name']]);
                } else {
                    $message->addCc($email['address']);
                }
            }
        }

        // add any bcc addresses
        if (isset($args['bcc']) && is_array($args['bcc'])) {
            foreach ($args['bcc'] as $email) {
                if (isset($email['name'])) {
                    $message->addBcc([$email['address'] => $email['name']]);
                } else {
                    $message->addBcc($email['address']);
                }
            }
        }

        return $mailer->sendMessage(
            $message,
            $args['subject'],
            $args['body'],
            isset($args['altbody']) ? $args['altbody'] : '',
            isset($args['html']) ? $args['html'] : '',
            isset($args['headers']) ? (is_array($args['headers']) ? $args['headers'] : [$args['headers']]) : [],
            isset($args['attachments']) ? $args['attachments'] : [],
            isset($args['stringattachments']) ? $args['stringattachments'] : [],
            isset($args['embeddedimages']) ? $args['embeddedimages'] : []
        );
    }
}
