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

use Gedmo\Blameable\BlameableListener;
use ServiceUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UserUtil;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class BlameListener overrides Stof\DoctrineExtensionsBundle\EventListener\BlameListener
 */
class BlameListener implements EventSubscriberInterface
{
    /**
     * @var BlameableListener
     */
    private $blameableListener;

    public function __construct(BlameableListener $blameableListener, $tokenStorage = null, AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $this->blameableListener = $blameableListener;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $em = ServiceUtil::get('doctrine.entitymanager');
        try {
            if (\System::isInstalling()) {
                $uid = 2;
            } else {
                $uid = UserUtil::getVar('uid');
            }
            $user = $em->getReference('ZikulaUsersModule:UserEntity', $uid);
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
