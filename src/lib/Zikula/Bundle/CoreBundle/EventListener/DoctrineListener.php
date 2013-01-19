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
use Zikula_Doctrine2_ZikulaSqlLogger;
use Zikula_Doctrine2_MySqlGenerateSchemaListener;

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

        // register namespace
        // Because the standard kernel classloader already has Doctrine registered as a namespace
        // we have to add a new loader onto the spl stack.
        $autoloader = new \Symfony\Component\ClassLoader\ClassLoader();
        $autoloader->register();
        $autoloader->addPrefix('DoctrineProxy', __DIR__.'/../../../../../ztemp/doctrinemodels');

        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];
        $dbConfig = array('host' => $config['host'],
                          'user' => $config['user'],
                          'password' => $config['password'],
                          'dbname' => $config['dbname'],
                          'driver' => 'pdo_' . $config['dbdriver'],
                          );
        $r = new \ReflectionClass('Doctrine\Common\Cache\\' . $this->container['dbcache.type'] . 'Cache');
        $dbCache = $r->newInstance();
        $ORMConfig = new \Doctrine\ORM\Configuration;
        $this->container->set('doctrine.configuration', $ORMConfig);
        $ORMConfig->setMetadataCacheImpl($dbCache);

        // create proxy cache dir
        CacheUtil::createLocalDir('doctrinemodels');

        // setup annotations base (probably not needed)
        AnnotationRegistry::registerFile(__DIR__.'/../../../../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

        // setup annotation reader
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $cacheReader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
        $this->container->set('doctrine.annotationreader', $cacheReader);

        // setup annotation driver
        $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($cacheReader);
        $this->container->set('doctrine.annotationdriver', $annotationDriver);

        // setup driver chains
        $driverChain = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
        $this->container->set('doctrine.driverchain', $driverChain);

        // configure Doctrine ORM
        $ORMConfig->setMetadataDriverImpl($annotationDriver);
        $ORMConfig->setQueryCacheImpl($dbCache);
        $ORMConfig->setProxyDir(CacheUtil::getLocalDir('doctrinemodels'));
        $ORMConfig->setProxyNamespace('DoctrineProxy');
        //$ORMConfig->setAutoGenerateProxyClasses(System::isDevelopmentMode());

        if (isset($serviceManager['log.enabled']) && $serviceManager['log.enabled']) {
            $ORMConfig->setSQLLogger(new \Zikula_Doctrine2_ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $eventManager = new \Doctrine\Common\EventManager;
        $this->container->set('doctrine.eventmanager', $eventManager);

         // setup MySQL specific listener (storage engine and encoding)
        if ($config['dbdriver'] == 'mysql') {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $eventManager->addEventSubscriber($mysqlSessionInit);

            $mysqlStorageEvent = new \Zikula_Doctrine2_MySqlGenerateSchemaListener($eventManager);
        }

        // setup the doctrine entitymanager
        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $ORMConfig, $eventManager);
        $this->container->set('doctrine.entitymanager', $entityManager);
    }

    public function initDoctrineExtensions(GenericEvent $event)
    {
        $definition = new Definition('Zikula_Doctrine2_ExtensionsManager', array(new Reference('doctrine.eventmanager'), new Reference('service_container')));
        $this->container->setDefinition('doctrine_extensions', $definition);

        $types = array(
            'Loggable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree',
            'Sortable', 'SoftDeletable', 'Blameable', 'Uploadable'
        );
        foreach ($types as $type) {
            // The listener for Translatable is incorrectly named TranslationListener
            if ($type != "Translatable") {
                $definition = new Definition("Gedmo\\$type\\{$type}Listener");
            } else {
                $definition = new Definition("Gedmo\\Translatable\\TranslationListener");
            }
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