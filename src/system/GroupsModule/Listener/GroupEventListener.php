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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Event\GroupApplicationPostCreatedEvent;
use Zikula\GroupsModule\Event\GroupApplicationPostProcessedEvent;

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
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        RouterInterface $router
    ) {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            GroupApplicationPostProcessedEvent::class => ['applicationProcessed'],
            GroupApplicationPostCreatedEvent::class => ['newApplication']
        ];
    }

    /**
     * Send an email to the user with results when a group application is processed.
     */
    public function applicationProcessed(GroupApplicationPostProcessedEvent $event): void
    {
        $applicationEntity = $event->getGroupApplication();
        $title = $this->translator->trans('Regarding your %groupName% group membership application', ['%groupName%' => $applicationEntity->getGroup()->getName()]);
        $siteName = $this->variableApi->getSystemVar('sitename');
        $adminMail = $this->variableApi->getSystemVar('adminmail');

        $user = $applicationEntity->getUser();
        $email = (new Email())
            ->from(new Address($adminMail, $siteName))
            ->to(new Address($user->getEmail(), $user->getUname()))
            ->subject($title)
            ->html($title . '\n\n' . $event->getMessage())
        ;
        $this->mailer->send($email);
    }

    /**
     * Send an email to the admin when a new group application is created.
     */
    public function newApplication(GroupApplicationPostCreatedEvent $event): void
    {
        if (!$this->variableApi->get('ZikulaGroupsModule', 'mailwarning')) {
            return;
        }
        $applicationEntity = $event->getGroupApplication();
        $body = $this->translator->trans('A new application has been created by %userName% to %groupName%. Please attend to this request at %url%', [
            '%userName%' => $applicationEntity->getUser()->getUname(),
            '%groupName%' => $applicationEntity->getGroup()->getName(),
            '%url%' => $this->router->generate('zikulagroupsmodule_group_adminlist', [], RouterInterface::ABSOLUTE_URL)
        ]);
        $adminMail = $this->variableApi->getSystemVar('adminmail');
        $siteName = $this->variableApi->getSystemVar('sitename');

        $email = (new Email())
            ->from(new Address($adminMail, $siteName))
            ->to(new Address($adminMail, $siteName))
            ->subject($this->translator->trans('New group application'))
            ->html($body)
        ;
        $this->mailer->send($email);
    }
}
