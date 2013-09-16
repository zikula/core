<?php

namespace Zikula\Bundle\CoreBundle\EventListener;

use Gedmo\Loggable\LoggableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;
use UserUtil;
use ServiceUtil;
use System;

use Gedmo\Blameable\BlameableListener;

/**
 * BlameableListener
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class BlameListener implements EventSubscriberInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var BlameableListener
     */
    private $blameableListener;

    public function __construct(BlameableListener $blameableListener, SecurityContextInterface $securityContext = null)
    {
        $this->blameableListener = $blameableListener;
//        $this->securityContext = $securityContext;
    }

    /**
     * Set the username from the security context by listening on core.request
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $em = ServiceUtil::get('doctrine.entitymanager');
        if (System::isInstalling()) {
            // on system install the user is not created yet...
            $user = null;
        } else {
            $uid = UserUtil::getVar('uid');
            $user = $em->getRepository('Zikula\Module\UsersModule\Entity\UserEntity')->findOneBy(array('uid' => $uid));
        }
        $this->blameableListener->setUserValue($user);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
