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
        /**
         * SwiftMail Parameters:
         * http://symfony.com/doc/current/cookbook/email/email.html#configuration
         *
         * transport (smtp, mail, sendmail, or gmail)
         * username
         * password
         * host
         * port
         * encryption (tls, or ssl)
         * auth_mode (plain, login, or cram-md5)
         * spool
         *      type (how to queue the messages, file or memory is supported, see How to Spool Emails)
         *      path (where to store the messages)
         * delivery_address (an email address where to send ALL emails)
         * disable_delivery (set to true to disable delivery completely)
         */
        $transport = MailerApi::$transportTypes[$mailerVars['mailertype']];
        $this->container->setParameter('swiftmailer', array(
            'transport' => $transport,
            'username' => $mailerVars['smtpusername'],
            'password' => $mailerVars['smtppassword'],
            'host' => $mailerVars['smtpserver'],
            'port' => $mailerVars['smtpport'],
            'encryption' => $mailerVars['smtpsecuremethod'],
            'auth_mode' => $mailerVars['smtpauth'],
            'spool' => array('type' => 'memory'),
            'delivery_address' => null,
            'disable_delivery' => false,
        ));
    }

}