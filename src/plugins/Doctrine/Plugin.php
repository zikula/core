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
class SystemPlugin_Doctrine_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface
{
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
        $this->_setup();

        $meta = $this->getMeta();
        if (!isset($meta['displayname']) && !isset($meta['description']) && !isset($meta['version'])) {
            throw new InvalidArgumentException(sprintf('%s->getMeta() must be implemented according to the abstract.  See docblock in Zikula_AbstractPlugin for details', get_class($this)));
        }

        // Load any handlers if they exist
        if ($this->getReflection()->hasMethod('setupHandlerDefinitions')) {
            $this->setupHandlerDefinitions();
        }
    }

    private function _setup()
    {
        $this->className = get_class($this);
        $this->serviceId = PluginUtil::getServiceId($this->className);
        $this->baseDir = dirname($this->getReflection()->getFileName());

        $this->moduleName = 'zikula';
        $this->pluginName = 'Doctrine';
        $this->pluginType = self::TYPE_SYSTEM;
        $this->domain = 'systemplugin_doctrine';

        $this->meta = $this->getMeta();
    }

    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Doctrine'),
                     'description' => $this->__('Provides Doctrine ORM, DBAL (2.1.3) and Common layers of Doctrine'),
                     'version'     => '2.1.4'
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
        $autoloader->register('DoctrineProxy', 'ztemp/doctrinemodels', '\\');

        $serviceManager = $this->eventManager->getServiceManager();
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
        include_once 'lib/vendor/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';

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
        //$ORMConfig->setAutoGenerateProxyClasses(System::isDevelopmentMode());

        if (isset($serviceManager['log.enabled']) && $serviceManager['log.enabled']) {
            $ORMConfig->setSQLLogger(new SystemPlugin_Doctrine_ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $eventManager = new \Doctrine\Common\EventManager;
        $serviceManager->attachService('doctrine.eventmanager', $eventManager);

         // setup MySQL specific listener (storage engine and encoding)
        if ($config['dbdriver'] == 'mysql') {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $eventManager->addEventSubscriber($mysqlSessionInit);

            $mysqlStorageEvent = new SystemPlugin_Doctrine_MySqlGenerateSchemaListener($eventManager);
        }

        // setup the doctrine entitymanager
        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $ORMConfig, $eventManager);
        $serviceManager->attachService('doctrine.entitymanager', $entityManager);
    }
}
