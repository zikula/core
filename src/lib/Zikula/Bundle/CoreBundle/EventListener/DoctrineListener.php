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
        if ($this->container->has('doctrine.entitymanager')) {
            return;
        }

        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];

        $r = new \ReflectionClass('Doctrine\Common\Cache\\' . $this->container['dbcache.type'] . 'Cache');

        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $ORMConfig = $em->getConfiguration();
        $this->container->set('doctrine.configuration', $ORMConfig); // to deprecated (drak)

        // create proxy cache dir
        CacheUtil::createLocalDir('doctrinemodels');

        // setup annotation reader
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $cacheReader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
        $this->container->set('doctrine.annotation_reader', $cacheReader);

        // setup annotation driver
        $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($cacheReader);
        $this->container->set('doctrine.annotation_driver', $annotationDriver);

        // add annotations as default driver
        $ORMConfig->getMetadataDriverImpl()->setDefaultDriver($annotationDriver);
        $this->container->set('doctrine.driver_chain', $ORMConfig->getMetadataDriverImpl());

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

        $this->container->setAlias('doctrine.entitymanager', 'doctrine.orm.default_entity_manager');
        $this->container->setAlias('doctrine.eventmanager', 'doctrine.event_manager');
    }

    public function initDoctrineExtensions(GenericEvent $event)
    {
        $definition = new Definition('Zikula\Core\Doctrine\ExtensionsManager', array(new Reference('doctrine.event_manager'), new Reference('service_container')));
        $this->container->setDefinition('doctrine_extensions', $definition);

        $types = array(
            'Loggable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree',
            'Sortable', 'SoftDeleteable', 'Blameable', 'Uploadable'
        );
        foreach ($types as $type) {
            $definition = new Definition("Gedmo\\$type\\{$type}Listener");
            $this->container->setDefinition(strtolower("doctrine_extensions.listener.$type"), $definition);
        }

        $config = $this->container->get('doctrine.configuration');
        $config->addFilter('soft-deleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');

        $definition = new Definition("DoctrineExtensions\\StandardFields\\StandardFieldsListener");
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
