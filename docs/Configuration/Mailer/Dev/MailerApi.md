---
currentMenu: mailer
---
# MailerApi

Interface: `\Zikula\MailerModule\Api\ApiInterface\MailerApiInterface`.  
Class: `\Zikula\MailerModule\Api\MailerApi`.

This class is used to send a mail using SwiftMailer configured with settings from the Mailer module configuration.

The class makes the following method available:

```php
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
public function sendMessage(
    Swift_Message $message,
    string $subject = null,
    string $body = null,
    string $altBody = '',
    bool $html = false,
    array $headers = [],
    array $attachments = [],
    array $stringAttachments = [],
    array $embeddedImages = []
): bool;
```

The class is fully tested.

The fastest way to use this Api:

```php
$message = new Swift_Message('my subject', 'the body text');
$message->setFrom('admin@example.com');
$message->setTo('foo@bar.com');
$this->mailerAPi->sendMessage($message);
```

Another example for using this service can be found in the `Zikula\MailerModule\Controller\ConfigController#testAction(Request $request)` method:

```php
// create new message instance
$message = new Swift_Message();

$message->setFrom([$adminMail => $siteName]);
$message->setTo([$formData['toAddress'] => $formData['toName']]);

$result = $this->mailerApi->sendMessage($message, $formData['subject'], $msgBody, $altBody, $html);
```
