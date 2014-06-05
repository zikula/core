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
        return array(
            //KernelEvents::REQUEST => 'onKernelRequest',
            'core.postinit' => 'onPostInit',
        );
    }
}
