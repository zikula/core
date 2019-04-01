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

namespace Zikula\GroupsModule\Listener;

use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\GroupEvents;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;

class GroupEventListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var MailerApiInterface
     */
    protected $mailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        MailerApiInterface $mailerApi,
        RouterInterface $router
    ) {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->mailer = $mailerApi;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            GroupEvents::GROUP_APPLICATION_PROCESSED => ['applicationProcessed'],
            GroupEvents::GROUP_NEW_APPLICATION => ['newApplication']
        ];
    }

    /**
     * Send an email to the user with results when a group application is processed.
     */
    public function applicationProcessed(GenericEvent $event): void
    {
        $applicationEntity = $event->getSubject();
        $formData = $event->getArguments();
        $title = $this->translator->__f('Regarding your %s group membership application', ['%s' => $applicationEntity->getGroup()->getName()]);
        $siteName = $this->variableApi->getSystemVar('sitename');
        $adminMail = $this->variableApi->getSystemVar('adminmail');

        $message = new Swift_Message();
        $message->setFrom([$adminMail => $siteName]);
        $user = $applicationEntity->getUser();
        $message->setTo([$user->getEmail() => $user->getUname()]);
        $this->mailer->sendMessage($message, $title, $title . '\n\n' . $formData['reason']);
    }

    /**
     * Send an email to the admin when a new group application is created.
     */
    public function newApplication(GenericEvent $event): void
    {
        if (!$this->variableApi->get('ZikulaGroupsModule', 'mailwarning')) {
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

        $message = new Swift_Message();
        $message->setFrom([$adminMail => $siteName]);
        $message->setTo([$adminMail => $siteName]);
        $this->mailer->sendMessage($message, $this->translator->__('New group application'), $body);
    }
}
