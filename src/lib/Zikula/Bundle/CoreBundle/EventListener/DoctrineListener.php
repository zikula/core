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

use CacheUtil;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Doctrine\Logger\ZikulaSqlLogger;
use Zikula\Core\Doctrine\Listener\MySqlGenerateSchemaListener;
use Doctrine\ORM\EntityManager;
use Zikula\Core\Doctrine\ExtensionsManager;
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
        if ($this->container->has('doctrine.event_manager')) {
            return;
        }

        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];

        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var $ORMConfig Configuration */
        $ORMConfig = $em->getConfiguration();

        $chain = $ORMConfig->getMetadataDriverImpl(); // driver chain
        $defaultAnnotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
            $this->container->get('annotation_reader')
        );
        $chain->setDefaultDriver($defaultAnnotationDriver);


        if (isset($serviceManager['log.enabled']) && $serviceManager['log.enabled']) {
            $ORMConfig->setSQLLogger(new ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $this->container->set('doctrine.event_manager', $eventManager = $em->getEventManager());

        // setup MySQL specific listener (storage engine and encoding)
        if ($config['dbdriver'] == 'mysql') {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $eventManager->addEventSubscriber($mysqlSessionInit);

            $mysqlStorageEvent = new MySqlGenerateSchemaListener($eventManager);
        }

        $this->container->setAlias('doctrine.eventmanager', 'doctrine.event_manager');
    }

    public function initDoctrineExtensions(GenericEvent $event)
    {
        // todo - migrate to XML
        $definition = new Definition("Zikula\\Core\\Doctrine\\StandardFields\\StandardFieldsListener");
        $this->container->setDefinition(strtolower("doctrine_extensions.listener.standardfields"), $definition);
    }

    public static function getSubscribedEvents()
    {
        return array('doctrine.boot' => array(
            array('initDoctrine', 100),
            array('initDoctrineExtensions', 100),
        ));
    }
}
