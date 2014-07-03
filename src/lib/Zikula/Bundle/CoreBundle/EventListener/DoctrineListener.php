<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Doctrine\Logger\ZikulaSqlLogger;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Doctrine\Listener\MySqlGenerateSchemaListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;

/**
 * Event handler to override templates.
 */
class DoctrineListener implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public function initDoctrine(GenericEvent $event)
    {
        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var $ORMConfig Configuration */
        $ORMConfig = $em->getConfiguration();

        $chain = $ORMConfig->getMetadataDriverImpl(); // driver chain
        $defaultAnnotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($this->container->get('annotation_reader'));
        $chain->setDefaultDriver($defaultAnnotationDriver);

        if (isset($serviceManager['log.enabled']) && $serviceManager['log.enabled']) {
            $ORMConfig->setSQLLogger(new ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $eventManager = $em->getEventManager();
        $this->container->set('doctrine.event_manager', $eventManager);

        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];
        // setup MySQL specific listener (storage engine and encoding)
        if ($config['dbdriver'] == 'mysql') {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $eventManager->addEventSubscriber($mysqlSessionInit);

            new MySqlGenerateSchemaListener($eventManager);
        }
    }

    public static function getSubscribedEvents()
    {
        return array('doctrine.boot' => array(
            array('initDoctrine', 100),
        ));
    }
}
