<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Mailer
 */

/**
 * API function to send e-mail message
 * @author Mark West
 * @param string args['fromname'] name of the sender
 * @param string args['fromaddress'] address of the sender
 * @param string args['toname '] name to the recipient
 * @param string args['toaddress'] the address of the recipient
 * @param string args['replytoname '] name to reply to
 * @param string args['replytoaddress'] address to reply to
 * @param string args['subject'] message subject
 * @param string args['contenttype '] optional contenttype of the mail (default config)
 * @param string args['charset'] optional charset of the mail (default config)
 * @param string args['encoding'] optional mail encoding (default config)
 * @param string args['body'] message body, if altbody is provided then this
 *                  is the HTML version of the body
 * @param string args['altbody'] alternative plain-text message body, if
 *                  specified the e-mail will be sent as multipart/alternative
 * @param array  args['cc'] addresses to add to the cc list
 * @param array  args['bcc'] addresses to add to the bcc list
 * @param array|string args['headers'] custom headers to add
 * @param int args['html'] HTML flag, if altbody is not specified then this
 *                  indicates whether body contains HTML or not; if altbody is
 *                  specified, then this value is ignored, the body is assumed
 *                  to be HTML, and the altbody is assumed to be plain text
 * @param array args['attachments'] array of either absolute filenames to attach
 *                  to the mail or array of arays in format
 *                  array($string,$filename,$encoding,$type)
 * @param array args['stringattachments'] array of arrays to treat as attachments,
 *                  format array($string,$filename,$encoding,$type)
 * @param array args['embeddedimages'] array of absolute filenames to image files
 *                  to embed in the mail
 * @todo Loading of language file based on Zikula language
 * @return bool true if successful, false otherwise
 */
function Mailer_userapi_sendmessage($args)
{
    // Check for installed advanced Mailer module
    $processed = (isset($args['processed']) ? (int) $args['processed'] : 0);
    if (ModUtil::available('advMailer') && ($processed != 1)) {
        return ModUtil::apiFunc('advMailer', 'user', 'sendmessage', $args);
    }

    // include php mailer class file
    if (file_exists($file = "system/Mailer/pnincludes/class.phpmailer.php")) {
        Loader::requireOnce($file);
    } else {
        return false;
    }

    // create new instance of mailer class
    $mail = new phpmailer();

    // set default message parameters
    $mail->PluginDir = "system/Mailer/pnincludes/";
    $mail->ClearAllRecipients();
    $mail->ContentType = isset($args['contenttype']) ? $args['contenttype'] : ModUtil::getVar('Mailer', 'contenttype');
    $mail->CharSet     = isset($args['charset'])     ? $args['charset']     : ModUtil::getVar('Mailer', 'charset');
    $mail->Encoding    = isset($args['encoding'])    ? $args['encoding']    : ModUtil::getVar('Mailer', 'encoding');
    $mail->WordWrap    = ModUtil::getVar('Mailer', 'wordwrap');

    // load the language file
    $mail->SetLanguage('en', $mail->PluginDir . 'language/');

    // get MTA configuration
    if (ModUtil::getVar('Mailer', 'mailertype') == 4) {
        $mail->IsSMTP();  // set mailer to use SMTP
        $mail->Host = ModUtil::getVar('Mailer', 'smtpserver');  // specify server
        $mail->Port = ModUtil::getVar('Mailer', 'smtpport');    // specify port
    } else if (ModUtil::getVar('Mailer', 'mailertype') == 3) {
        $mail->IsQMail();  // set mailer to use QMail
    } else if (ModUtil::getVar('Mailer', 'mailertype') == 2) {
        ini_set("sendmail_from", $args['fromaddress']);
        $mail->IsSendMail();  // set mailer to use SendMail
        $mail->Sendmail = ModUtil::getVar('Mailer', 'sendmailpath'); // specify Sendmail path
    } else {
        $mail->IsMail();  // set mailer to use php mail
    }

    // set authentication paramters if required
    if (ModUtil::getVar('Mailer', 'smtpauth') == 1) {
        $mail->SMTPAuth = true; // turn on SMTP authentication
        $mail->Username = ModUtil::getVar('Mailer', 'smtpusername');  // SMTP username
        $mail->Password = ModUtil::getVar('Mailer', 'smtppassword');  // SMTP password
    }

    // set HTML mail if required
    if (isset($args['html']) && is_bool($args['html'])) {
        $mail->IsHTML($args['html']); // set email format to HTML
    } else {
        $mail->IsHTML(ModUtil::getVar('Mailer', 'html')); // set email format to the default
    }

    // set fromname and fromaddress, default to 'sitename' and 'adminmail' config vars
    $mail->FromName = (isset($args['fromname']) && $args['fromname']) ? $args['fromname'] : pnConfigGetVar('sitename');
    $mail->From     = (isset($args['fromaddress']) && $args['fromaddress']) ? $args['fromaddress'] : pnConfigGetVar('adminmail');

    // add any to addresses
    if (is_array($args['toaddress'])) {
        $i = 0;
        foreach ($args['toaddress'] as $toadd) {
            isset($args['toname'][$i]) ? $tona = $args['toname'][$i] : $tona = $toadd;
            $mail->AddAddress($toadd, $tona);
            $i++;
        }
    } else {
         // $toaddress is not an array -> old logic
        $tona = '';
        if (isset($args['toname'])) {
            $tona = $args['toname'];
        }
        // process multiple names entered in a single field separated by commas (#262)
        foreach (explode(',', $args['toaddress']) as $toadd) {
            $mail->AddAddress($toadd, ($tona == '') ? $toadd : $tona);
        }
    }

    // if replytoname and replytoaddress have been provided us them
    // otherwise take the fromaddress, fromname we build earlier
    if (!isset($args['replytoname']) || empty($args['replytoname'])) {
        $args['replytoname'] = $mail->FromName;
    }
    if (!isset($args['replytoaddress'])  || empty($args['replytoaddress'])) {
          $args['replytoaddress'] = $mail->From;
    }
    $mail->AddReplyTo($args['replytoaddress'], $args['replytoname']);

    // add any cc addresses
    if (isset($args['cc']) && is_array($args['cc'])) {
        foreach ($args['cc'] as $email) {
            if (isset($email['name'])) {
                $mail->AddCC($email['address'], $email['name']);
            } else {
                $mail->AddCC($email['address']);
            }
        }
    }

    // add any bcc addresses
    if (isset($args['bcc']) && is_array($args['bcc'])) {
        foreach ($args['bcc'] as $email) {
            if (isset($email['name'])) {
                $mail->AddBCC($email['address'], $email['name']);
            } else {
                $mail->AddBCC($email['address']);
            }
        }
    }

    // add any custom headers
    if (isset($args['headers']) && is_string($args['headers'])) {
        $args['headers'] = explode ("\n", $args['headers']);
    }
    if (isset($args['headers']) && is_array($args['headers'])) {
        foreach ($args['headers'] as $header) {
            $mail->AddCustomHeader($header);
        }
    }

    // add message subject and body
    $mail->Subject = $args['subject'];
    $mail->Body    = $args['body'];
    if (isset($args['altbody']) && !empty($args['altbody'])) {
        $mail->AltBody = $args['altbody'];
    }

    // add attachments
    if (isset($args['attachments']) && !empty($args['attachments'])) {
        foreach($args['attachments'] as $attachment) {
            if (is_array($attachment)) {
                if (count($attachment) != 4) {
                    // skip invalid arrays
                    continue;
                }
                $mail->AddAttachment($attachment[0], $attachment[1], $attachment[2], $attachment[3]);
            } else {
                $mail->AddAttachment($attachment);
            }
        }
    }

    // add string attachments.
    if (isset($args['stringattachments']) && !empty($args['stringattachments'])) {
        foreach($args['stringattachments'] as $attachment) {
            if (is_array($attachment) && count($attachment) == 4) {
                $mail->AddStringAttachment($attachment[0], $attachment[1], $attachment[2], $attachment[3]);
            }
        }
    }

    // add embedded images
    if (isset($args['embeddedimages']) && !empty($args['embeddedimages'])) {
        foreach($args['embeddedimages'] as $embeddedimage) {
            $ret = $mail->AddEmbeddedImage($embeddedimage['path'],
                                           $embeddedimage['cid'],
                                           $embeddedimage['name'],
                                           $embeddedimage['encoding'],
                                           $embeddedimage['type']);
        }
    }

    // send message
    if (!$mail->Send()) {
        // message not send
        $args['errorinfo'] = ($mail->IsError()) ? $mail->ErrorInfo : __('Error! An unidentified problem occurred while sending the e-mail message.');
        LogUtil::log(__f('Error! A problem occurred while sending an e-mail message from \'%1$s\' (%2$s) to (%3$s) (%4$s) with the subject line \'%5$s\': %6$s', $args));
        if (SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerError($args['errorinfo']);
        } else {
            return LogUtil::registerError(__('Error! A problem occurred while sending the e-mail message.'));
        }
    }
    return true; // message sent
}
