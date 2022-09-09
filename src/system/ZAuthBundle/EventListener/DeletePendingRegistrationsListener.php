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

namespace Zikula\ZAuthBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\UsersBundle\Event\RegistrationPostDeletedEvent;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

class DeletePendingRegistrationsListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserVerificationRepositoryInterface $userVerificationRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $mailLogger, // $mailLogger var name auto-injects the mail channel handler
        private readonly TranslatorInterface $translator,
        private readonly SiteDefinitionInterface $site,
        private readonly int $registrationExpireDays,
        private readonly bool $mailLoggingEnabled
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['delete'],
            RegistrationPostDeletedEvent::class => ['sendEmail'],
        ];
    }

    public function delete(TerminateEvent $event): void
    {
        // remove expired registrations
        if (0 < $this->registrationExpireDays) {
            $deletedUsers = $this->userVerificationRepository->purgeExpiredRecords($this->registrationExpireDays, ZAuthConstant::VERIFYCHGTYPE_REGEMAIL, true);
            foreach ($deletedUsers as $deletedUser) {
                $this->eventDispatcher->dispatch(new RegistrationPostDeletedEvent($deletedUser));
            }
        }
    }

    public function sendEmail(RegistrationPostDeletedEvent $event): void
    {
        $siteName = $this->site->getName();
        $email = (new Email())
            ->from(new Address($this->site->getAdminMail(), $siteName))
            ->to(new Address($event->getUser()->getEmail(), $event->getUser()->getUname()))
            ->subject($this->translator->trans('Registration deleted at %site%', ['%site%' => $siteName]))
            ->text($this->translator->trans(<<<'EOT'
Your registration at %site% associated with this email (%email%) has been deleted from the site.
This could have happened because you have delayed too long in confirming your email address, or because the administrator manually deleted your registration.
If you have any questions, please contact the site administrator or re-register.
EOT
        , ['%site%' => $siteName, '%email%' => $event->getUser()->getEmail()]));
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->mailLogger->error($exception->getMessage(), [
                'in' => __METHOD__,
            ]);
        }
        if ($this->mailLoggingEnabled) {
            $this->mailLogger->info(sprintf('Email sent to %s', $event->getUser()->getEmail()), [
                'in' => __METHOD__,
            ]);
        }
    }
}