<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Doctrine plugin definition.
 */
class DoctrineListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'core.preinit' => array('initialize'),
            );
    }

    /**
     * Initialise.
     *
     * Runs at plugin init time.
     *
     * @return void
     */
    public function initialize(GenericEvent $event)
    {
        // register namespace
        // Because the standard kernel classloader already has Doctrine registered as a namespace
        // we have to add a new loader onto the spl stack.
        $autoloader = new UniversalClassLoader();
        $autoloader->register();
        $autoloader->registerNamespaces(array(
            'DoctrineProxy' => 'ztemp/doctrinemodels',
            ));

        $container = $event->getDispatcher()->getContainer();
        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];
        $dbConfig = array('host' => $config['host'],
                          'user' => $config['user'],
                          'password' => $config['password'],
                          'dbname' => $config['dbname'],
                          'driver' => 'pdo_' . $config['dbdriver'],
                          );
        $r = new \ReflectionClass('Doctrine\Common\Cache\\' . $container['dbcache.type'] . 'Cache');
        $dbCache = $r->newInstance();
        $ORMConfig = new \Doctrine\ORM\Configuration;
        $container->set('doctrine.configuration', $ORMConfig);
        $ORMConfig->setMetadataCacheImpl($dbCache);

        // create proxy cache dir
        \CacheUtil::createLocalDir('doctrinemodels');

        // setup annotations base
        include_once \ZLOADER_PATH . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';

        // setup annotation reader
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $cacheReader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
        $container->set('doctrine.annotationreader', $cacheReader);

        // setup annotation driver
        $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($cacheReader);
        $container->set('doctrine.annotationdriver', $annotationDriver);

        // setup driver chains
        $driverChain = new \Doctrine\ORM\Mapping\Driver\DriverChain();
        $container->set('doctrine.driverchain', $driverChain);

        // configure Doctrine ORM
        $ORMConfig->setMetadataDriverImpl($annotationDriver);
        $ORMConfig->setQueryCacheImpl($dbCache);
        $ORMConfig->setProxyDir(\CacheUtil::getLocalDir('doctrinemodels'));
        $ORMConfig->setProxyNamespace('DoctrineProxy');

        if (isset($container['log.enabled']) && $container['log.enabled']) {
            $ORMConfig->setSQLLogger(new \Zikula\Core\Doctrine\Logger\ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $dispatcher = new \Doctrine\Common\EventManager;
        $container->set('doctrine.eventmanager', $dispatcher);

         // setup MySQL specific listener (storage engine and encoding)
        if ($config['dbdriver'] == 'mysql') {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $dispatcher->addEventSubscriber($mysqlSessionInit);
        }

        // setup the doctrine entitymanager
        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $ORMConfig, $dispatcher);
        $container->set('doctrine.entitymanager', $entityManager);
    }
}
