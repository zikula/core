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
use Zikula\Core\Event\GenericEvent;

class BlameListener implements EventSubscriberInterface
{
    /**
     * @var BlameableListener
     */
    private $blameableListener;

    public function __construct(BlameableListener $blameableListener, SecurityContextInterface $securityContext = null)
    {
        $this->blameableListener = $blameableListener;
    }

    /**
     * required method as result of implementation
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // ...
    }

    public function onPostInit(GenericEvent $event)
    {
        if (\System::isInstalling()) {
            return;
        }

        $em = ServiceUtil::get('doctrine.entitymanager');
        $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy(array('uid' => UserUtil::getVar('uid')));
        $this->blameableListener->setUserValue($user);
    }

    /**
     * required implementation of abstract parent class
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            //KernelEvents::REQUEST => 'onKernelRequest',
            'core.postinit' => 'onPostInit',
        );
    }
}
