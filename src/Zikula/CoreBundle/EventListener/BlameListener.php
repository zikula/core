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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gedmo\Blameable\BlameableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\UsersModule\Constant;

/**
 * Class BlameListener overrides Stof\DoctrineExtensionsBundle\EventListener\BlameListener
 */
class BlameListener implements EventSubscriberInterface
{
    private bool $installed;

    public function __construct(
        private readonly BlameableListener $blameableListener,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        string $installed
    ) {
        $this->installed = '0.0.0' !== $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        try {
            $uid = Constant::USER_ID_ANONYMOUS;
            if (!$this->installed) {
                $uid = Constant::USER_ID_ADMIN;
            } else {
                $request = $this->requestStack->getCurrentRequest();
                if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                    $uid = $this->session->isStarted() ? $this->session->get('uid', Constant::USER_ID_ANONYMOUS) : $uid;
                }

            }
            $user = $this->entityManager->getReference('ZikulaUsersModule:UserEntity', $uid);
            $this->blameableListener->setUserValue($user);
        } catch (Exception) {
            // silently fail - likely installing and tables not available
        }
    }
}
