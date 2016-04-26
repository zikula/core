MailerApi
=========

classname: \Zikula\MailerModule\Api\MailerApi

service id="zikula_mailer_module.api.mailer"

This class is used to send a mail using SwiftMailer configured with settings from the Mailer module configuration.

The class makes the following methods available:

    - sendMessage(Swift_Message $message, $subject, $body, $altBody, $html, array $headers = [], array $attachments = [], array $stringAttachments = [], array $embeddedImages = [])

It is assumed that basic parameters for sender and recipient(s) have already been set.

One example for using this service can be found in the `Zikula\MailerModule\Controller\ConfigController#testAction(Request $request)` method:

```
    // create new message instance
    /** @var Swift_Message */
    $message = Swift_Message::newInstance();

    $message->setFrom([$adminMail => $sitename]);
    $message->setTo([$formData['toAddress'] => $formData['toName']]);

    $mailer = $this->get('zikula_mailer_module.api.mailer');
    $result = $mailer->sendMessage($message, $formData['subject'], $msgBody, $altBody, $html);
```
