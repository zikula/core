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

namespace Zikula\CoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Blameable\BlameableListener;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\UsersBundle\UsersConstant;

/**
 * Class BlameSubscriber overrides Stof\DoctrineExtensionsBundle\EventListener\BlameListener
 */
class BlameSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'stof_doctrine_extensions.listener.blameable')]
        private readonly BlameableListener $blameableListener,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        try {
            $uid = UsersConstant::USER_ID_ANONYMOUS;
            try {
                $request = $this->requestStack->getCurrentRequest();
                if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                    $uid = $session->isStarted() ? $session->get('uid', UsersConstant::USER_ID_ANONYMOUS) : $uid;
                }
            } catch (\Exception) {
                $uid = UsersConstant::USER_ID_ADMIN;
            }
            $user = $this->entityManager->getReference('ZikulaUsersBundle:User', $uid);
            $this->blameableListener->setUserValue($user);
        } catch (\Exception) {
            // silently fail - likely installing and tables not available
        }
    }
}
