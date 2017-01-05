<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\GroupsModule\GroupEvents;
use Zikula\MailerModule\Api\MailerApi;

class GroupEventListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    protected $variableApi;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var MailerApi
     */
    protected $mailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    public static function getSubscribedEvents()
    {
        return [
            GroupEvents::GROUP_APPLICATION_PROCESSED => ['applicationProcessed'],
            GroupEvents::GROUP_NEW_APPLICATION => ['newApplication']
        ];
    }

    /**
     * @param VariableApi $variableApi VariableApi service instance
     * @param TranslatorInterface $translator
     * @param MailerApi $mailerApi
     * @param RouterInterface $router
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator, MailerApi $mailerApi, RouterInterface $router)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->mailer = $mailerApi;
        $this->router = $router;
    }

    /**
     * Send an email to the user with results when a group application is processed
     * @param GenericEvent $event
     */
    public function applicationProcessed(GenericEvent $event)
    {
        $applicationEntity = $event->getSubject();
        $formData = $event->getArguments();
        $title = $this->translator->__f('Regarding your %s group membership application', ['%s' => $applicationEntity->getGroup()->getName()]);
        $siteName = $this->variableApi->getSystemVar('sitename');
        $adminMail = $this->variableApi->getSystemVar('adminmail');

        /** @var \Swift_Message */
        $message = \Swift_Message::newInstance();
        $message->setFrom([$adminMail => $siteName]);
        $user = $applicationEntity->getUser();
        $message->setTo([$user->getEmail() => $user->getUname()]);
        $this->mailer->sendMessage($message, $title, $title . '\n\n' . $formData['reason']);
    }

    /**
     * Send an email to the admin when a new group application is created
     * @param GenericEvent $event
     */
    public function newApplication(GenericEvent $event)
    {
        if (!$this->variableApi->get('ZikulaGroupsModule', 'mailwarning', false)) {
            return;
        }
        $applicationEntity = $event->getSubject();
        $body = $this->translator->__f('A new application has been created by %user to %group. Please attend to this request at %url', [
            '%user' => $applicationEntity->getUser()->getUname(),
            '%group' => $applicationEntity->getGroup()->getName(),
            '%url' => $this->router->generate('zikulagroupsmodule_group_adminlist', [], RouterInterface::ABSOLUTE_URL)
        ]);
        $adminMail = $this->variableApi->getSystemVar('adminmail');
        $siteName = $this->variableApi->getSystemVar('sitename');
        /** @var \Swift_Message */
        $message = \Swift_Message::newInstance();
        $message->setFrom([$adminMail => $siteName]);
        $message->setTo([$adminMail => $siteName]);
        $this->mailer->sendMessage($message, $this->translator->__('New group application'), $body);
    }
}
