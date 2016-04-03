<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class NotificationHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * NotificationHelper constructor.
     * @param TranslatorInterface $translator
     * @param \Twig_Environment $twig
     * @param VariableApi $variableApi
     */
    public function __construct(TranslatorInterface $translator, \Twig_Environment $twig, VariableApi $variableApi)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * @param string $toAddress The destination e-mail address.
     * @param string $notificationType The type of notification, converted to the name of a template
     *                                     in the form users_userapi_{type}mail.tpl and/or .txt.
     * @param array $templateArgs One or more arguments to pass to the renderer for use in the template.
     * @param string $subject The e-mail subject, overriding the template's subject.
     * @return bool
     */
    public function sendNotification($toAddress, $notificationType = '', array $templateArgs = [], $subject = '')
    {
        $mailerArgs = [];
        $mailerArgs['toaddress'] = $toAddress;
        $templateArgs['sitename'] = !isset($templateArgs['sitename']) ? $this->variableApi->get(VariableApi::CONFIG, 'sitename') : $templateArgs['sitename'];

        $templateName = "@ZikulaUsersModule/Email/{$notificationType}.html.twig";
        try {
            $this->twig->loadTemplate($templateName);
            $mailerArgs['html'] = true;
            $mailerArgs['body'] = $this->twig->render($templateName, $templateArgs);
        } catch (\Twig_Error_Loader $e) {
            // silent fail
        }

        $templateName = "@ZikulaUsersModule/Email/{$notificationType}.txt.twig";
        try {
            $this->twig->loadTemplate($templateName);
            if (isset($mailerArgs['body'])) {
                $bodyType = 'altbody';
                unset($mailerArgs['html']);
            } else {
                $bodyType = 'body';
                $mailerArgs['html'] = false;
            }
            $mailerArgs[$bodyType] = $this->twig->render($templateName, $templateArgs);
        } catch (\Twig_Error_Loader $e) {
            // silent fail
        }

        if (!empty($subject)) {
            $mailerArgs['subject'] = $subject;
        } else {
            $mailerArgs['subject'] = $this->generateEmailSubject($notificationType, $templateArgs);
        }

        if ($mailerArgs['body']) {
            return \ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendMessage', $mailerArgs);
        }

        return true;
    }

    private function generateEmailSubject($notificationType, array $templateArgs = [])
    {
        switch ($notificationType) {
            case 'activation':
                return $this->translator->__('Verify your account.');
                break;
            case 'confirmchemail':
                return $this->translator->__('Verify your new e-mail address.');
                break;
            case 'importnotify':
                return $this->translator->__f('Welcome to %s!', ['%s' => $templateArgs['sitename']]);
                break;
            case 'lostpasscode':
                return $this->translator->__('Recover your password.');
                break;
            case 'lostpassword':
                return $this->translator->__f('Reset your password at \'%s\'', ['%s' => $templateArgs['sitename']]);
                break;
            case 'lostuname':
                return $this->translator->__f('\'%s\' account information', ['%s' => $templateArgs['sitename']]);
                break;
            case 'regadminnotify':
                if ($templateArgs['reginfo']['isapproved']) {
                    return $this->translator->__f('New registration pending approval: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                } elseif ($templateArgs['reginfo']['isverified']) {
                    return $this->translator->__f('New registration pending verification: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                } else {
                    return $this->translator->__f('New user activated: %s', ['%s' => $templateArgs['reginfo']['uname']]);
                }
                break;
            case 'regdeny':
                return $this->translator->__f('Your recent request at %s.', ['%s' => $templateArgs['sitename']]);
                break;
            case 'regverifyemail':
                return $this->translator->__f('Verify your e-mail address for %s.', ['%s' => $templateArgs['sitename']]);
                break;
            case 'userveryifyemail':
                return $this->translator->__f('Verify your request to change your e-mail address at \'%s\'', ['%s' => $templateArgs['sitename']]);
                break;
            case 'welcome':
                return $this->translator->__f('Welcome to %1$s, %2$s!', ['%1$s' => $templateArgs['sitename'], '%2$s' => $templateArgs['reginfo']['uname']]);
                break;
            default:
                return $this->translator->__f('A message from %s.', ['%s' => $templateArgs['sitename']]);
        }
    }
}
