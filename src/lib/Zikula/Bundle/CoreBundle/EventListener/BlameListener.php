<?php

/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
 *
 * @package Zikula\Bundle\CoreBundle\EventListener
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
        $em = ServiceUtil::get('doctrine.orm.default_entity_manager');
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
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
