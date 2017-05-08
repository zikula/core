<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Blameable\BlameableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\UsersModule\Constant;

/**
 * Class BlameListener overrides Stof\DoctrineExtensionsBundle\EventListener\BlameListener
 */
class BlameListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BlameableListener
     */
    private $blameableListener;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        BlameableListener $blameableListener,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        $installed
    ) {
        $this->blameableListener = $blameableListener;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->installed = $installed;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            if (!$this->installed) {
                $uid = Constant::USER_ID_ADMIN;
            } else {
                $uid = $this->session->isStarted() ? $this->session->get('uid', Constant::USER_ID_ANONYMOUS) : Constant::USER_ID_ANONYMOUS;
            }
            $user = $this->entityManager->getReference('ZikulaUsersModule:UserEntity', $uid);
            $this->blameableListener->setUserValue($user);
        } catch (\Exception $e) {
            // silently fail - likely installing and tables not available
        }
    }

    /**
     * required implementation of abstract parent class
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
