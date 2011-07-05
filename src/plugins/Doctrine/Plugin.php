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
 * SwiftMailer plugin definition.
 */
class SystemPlugin_Doctrine_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Doctrine'),
                     'description' => $this->__('Provides Doctrine ORM, DBAL and Common 3.0.x layers of Doctrine'),
                     'version'     => '2.1.0'
                      );
    }

    /**
     * Initialise.
     *
     * Runs at plugin init time.
     *
     * @return void
     */
    public function initialize()
    {
        // register namespace
        // Because the standard kernel classloader already has Doctrine registered as a namespace
        // we have to add a new loader onto the spl stack.
        $autoloader = new Zikula_KernelClassLoader();
        $autoloader->spl_autoload_register();
        include 'lib/DoctrineHelper.php';
        $autoloader->register('Doctrine', dirname(__FILE__) . '/lib/vendor', '\\');
        $autoloader->register('Symfony\\Components\\Yaml', dirname(__FILE__) . '/lib/vendor', '\\');
        $autoloader->register('Symfony\\Components\\Console', dirname(__FILE__) . '/lib/vendor', '\\');
        $autoloader->register('DoctrineProxy', 'ztemp/doctrinemodels', '\\');

        $serviceManager = $this->eventManager->getServiceManager();
        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];
        $dbConfig = array('host' => $config['host'], 'user' => $config['user'], 'password' => $config['password'], 'dbname' => $config['dbname'], 'driver' => 'pdo_' . $config['dbdriver']);
        $r = new \ReflectionClass('Doctrine\Common\Cache\\' . $serviceManager['dbcache.type'] . 'Cache');
        $dbCache = $r->newInstance();
        $ORMConfig = new \Doctrine\ORM\Configuration;
        $serviceManager->attachService('doctrine.configuration', $ORMConfig);
        $ORMConfig->setMetadataCacheImpl($dbCache);
        require_once 'lib/vendor/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $cacheReader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
        
        $serviceManager->attachService('doctrine.annotationreader', $cacheReader);
        
        $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($cacheReader);
        $serviceManager->attachService('doctrine.annotationdriver', $annotationDriver);
        
        $driverChain = new \Doctrine\ORM\Mapping\Driver\DriverChain();
        $serviceManager->attachService('doctrine.driverchain', $driverChain);
        
        $ORMConfig->setMetadataDriverImpl($annotationDriver);
        $ORMConfig->setQueryCacheImpl($dbCache);
        $ORMConfig->setProxyDir('ztemp/doctrinemodels');
        $ORMConfig->setProxyNamespace('DoctrineProxy');
        //$ORMConfig->setAutoGenerateProxyClasses(System::isDevelopmentMode());

        $eventManager = new \Doctrine\Common\EventManager;
        $serviceManager->attachService('doctrine.eventmanager', $eventManager);
        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $ORMConfig, $eventManager);
        $serviceManager->attachService('doctrine.entitymanager', $entityManager);
    }
}
