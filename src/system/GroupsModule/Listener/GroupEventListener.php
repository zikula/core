<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $loggingEnabled;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var SiteDefinitionInterface
     */
    private $site;

    public function __construct(
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        LoggerInterface $mailLogger, // $mailLogger var name auto-injects the mail channel handler
        RouterInterface $router,
        SiteDefinitionInterface $site
    ) {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->logger = $mailLogger;
        $this->router = $router;
        $this->site = $site;
        $this->loggingEnabled = $variableApi->get('ZikulaMailerModule', 'enableLogging', false);
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
        $title = $this->translator->trans(
            'Regarding your %groupName% group membership application',
            [
                '%groupName%' => $applicationEntity->getGroup()->getName()
            ]
        );
        $adminMail = $this->variableApi->getSystemVar('adminmail');
        $siteName = $this->site->getName();

        $user = $applicationEntity->getUser();
        $email = (new Email())
            ->from(new Address($adminMail, $siteName))
            ->to(new Address($user->getEmail(), $user->getUname()))
            ->subject($title)
            ->html($title . '\n\n' . $event->getMessage())
        ;
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), [
                'in' => __METHOD__,
            ]);
        }
        if ($this->loggingEnabled) {
            $this->logger->info(sprintf('Email sent to %', $user->getEmail()), [
                'in' => __METHOD__,
            ]);
        }
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
        $body = $this->translator->trans(
            'A new application has been created by %userName% to %groupName%. Please attend to this request at %url%',
            [
                '%userName%' => $applicationEntity->getUser()->getUname(),
                '%groupName%' => $applicationEntity->getGroup()->getName(),
                '%url%' => $this->router->generate('zikulagroupsmodule_group_adminlist', [], RouterInterface::ABSOLUTE_URL)
            ]
        );
        $adminMail = $this->variableApi->getSystemVar('adminmail');
        $siteName = $this->site->getName();

        $email = (new Email())
            ->from(new Address($adminMail, $siteName))
            ->to(new Address($adminMail, $siteName))
            ->subject($this->translator->trans('New group application'))
            ->html($body)
        ;
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), [
                'in' => __METHOD__,
            ]);
        }
        if ($this->loggingEnabled) {
            $this->logger->info(sprintf('Email sent to %', $adminMail), [
                'in' => __METHOD__,
            ]);
        }
    }
}
