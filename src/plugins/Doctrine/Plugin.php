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
        return array('displayname' => $this->__('Doctrine ORM'),
                     'description' => $this->__('Provides Doctrine ORM, DBAL and Common layers of Doctrine 2'),
                     'version'     => '2.1.0-BETA1'
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
        $r = new ReflectionClass('Doctrine\Common\Cache\\' . $serviceManager['dbcache.type'] . 'Cache');
        $dbCache = $r->newInstance();
        $ORMConfig = new \Doctrine\ORM\Configuration;
        $ORMConfig->setMetadataCacheImpl($dbCache);
        $driverImpl = $ORMConfig->newDefaultAnnotationDriver();
        $ORMConfig->setMetadataDriverImpl($driverImpl);
        $ORMConfig->setQueryCacheImpl($dbCache);
        $ORMConfig->setProxyDir('ztemp/doctrinemodels');
        $ORMConfig->setProxyNamespace('DoctrineProxy');
        //$ORMConfig->setAutoGenerateProxyClasses(System::isDevelopmentMode());

        $eventManager = new \Doctrine\Common\EventManager;
        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $ORMConfig, $eventManager);
        $serviceManager->attachService('doctrine.entitymanager', $entityManager);
    }
}
