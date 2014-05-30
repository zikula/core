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

namespace Zikula\Module\MailerModule\Listener;

use ModUtil;
use System;
use SecurityUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Module\MailerModule\Api\AdminApi as MailerApi;

class CorePostInitListener implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'core.postinit' => array('config'),
        );
    }

    /**
     * Handle event "core.postinit".
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function config(GenericEvent $event)
    {
        $mailerVars = ModUtil::getVar('ZikulaMailerModule');
        $transport = MailerApi::$transportTypes[$mailerVars['mailertype']];
        $this->container->setParameter('swiftmailer', array('transport' => $transport));
    }

}