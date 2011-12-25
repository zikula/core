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

/**
 * Doctrine plugin definition.
 */
class DoctrineListener extends Zikula\Framework\AbstractEventHandler
{
    public function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('core.preinit', 'initialize');
    }

    /**
     * Initialise.
     *
     * Runs at plugin init time.
     *
     * @return void
     */
    public function initialize(Zikula\Common\EventManager\Event $event)
    {
        // register namespace
        // Because the standard kernel classloader already has Doctrine registered as a namespace
        // we have to add a new loader onto the spl stack.
        $autoloader = new Zikula\Common\KernelClassLoader();
        $autoloader->spl_autoload_register();
        $autoloader->register('Doctrine\\Common', ZLOADER_PATH . '/../vendor/doctrine-common/lib', '\\');
        $autoloader->register('Doctrine\\DBAL', ZLOADER_PATH . '/../vendor/doctrine-dbal/lib', '\\');
        $autoloader->register('Doctrine\\ORM', ZLOADER_PATH . '/../vendor/doctrine/lib', '\\');
        $autoloader->register('DoctrineProxy', 'ztemp/doctrinemodels', '\\');

        $serviceManager = $event->getDispatcher()->getServiceManager();
        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];
        $dbConfig = array('host' => $config['host'],
                          'user' => $config['user'],
                          'password' => $config['password'],
                          'dbname' => $config['dbname'],
                          'driver' => 'pdo_' . $config['dbdriver'],
                          );
        $r = new \ReflectionClass('Doctrine\Common\Cache\\' . $serviceManager['dbcache.type'] . 'Cache');
        $dbCache = $r->newInstance();
        $ORMConfig = new \Doctrine\ORM\Configuration;
        $serviceManager->attachService('doctrine.configuration', $ORMConfig);
        $ORMConfig->setMetadataCacheImpl($dbCache);

        // create proxy cache dir
        CacheUtil::createLocalDir('doctrinemodels');

        // setup annotations base
        include_once ZLOADER_PATH . '/../vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';

        // setup annotation reader
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $cacheReader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
        $serviceManager->attachService('doctrine.annotationreader', $cacheReader);

        // setup annotation driver
        $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($cacheReader);
        $serviceManager->attachService('doctrine.annotationdriver', $annotationDriver);

        // setup driver chains
        $driverChain = new \Doctrine\ORM\Mapping\Driver\DriverChain();
        $serviceManager->attachService('doctrine.driverchain', $driverChain);

        // configure Doctrine ORM
        $ORMConfig->setMetadataDriverImpl($annotationDriver);
        $ORMConfig->setQueryCacheImpl($dbCache);
        $ORMConfig->setProxyDir(CacheUtil::getLocalDir('doctrinemodels'));
        $ORMConfig->setProxyNamespace('DoctrineProxy');

        if (isset($serviceManager['log.enabled']) && $serviceManager['log.enabled']) {
            $ORMConfig->setSQLLogger(new \Zikula\Core\Doctrine\Logger\ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $eventManager = new \Doctrine\Common\EventManager;
        $serviceManager->attachService('doctrine.eventmanager', $eventManager);

         // setup MySQL specific listener (storage engine and encoding)
        if ($config['dbdriver'] == 'mysql') {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $eventManager->addEventSubscriber($mysqlSessionInit);

            $mysqlStorageEvent = new \Zikula\Core\Doctrine\Listener\MySqlGenerateSchemaListener($eventManager);
        }

        // setup the doctrine entitymanager
        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $ORMConfig, $eventManager);
        $serviceManager->attachService('doctrine.entitymanager', $entityManager);
    }
}
