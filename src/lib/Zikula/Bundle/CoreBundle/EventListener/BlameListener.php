<?php

namespace Zikula\Bundle\CoreBundle\EventListener;

use Gedmo\Loggable\LoggableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;
use UserUtil;
use ServiceUtil;

use Gedmo\Blameable\BlameableListener;

/**
 * BlameableListener
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class BlameListener implements EventSubscriberInterface
{
    public function __construct(BlameableListener $blameableListener, SecurityContextInterface $securityContext = null)
    {
        $em = ServiceUtil::get('doctrine.entitymanager');
        $user = $em->getRepository('Zikula\Module\UsersModule\Entity\UserEntity')->findOneBy(array('uid' => UserUtil::getVar('uid')));
        $blameableListener->setUserValue($user);
    }

    /**
     * required method as result of implementation
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
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
