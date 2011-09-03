<?php

namespace Zikula\ModulesBundle;

use Symfony\Component\HttpKernel\Kernel;

abstract class ZikulaKernel extends Kernel{
    private $moduleBundles;
    private $runtimeBundleMap = array();
    private $loadModules;
    
    
    public function __construct($environment, $debug, $loadModules=false)
    {
        parent::__construct($environment, $debug);
        
        $this->loadModules = $loadModules;
    }
    
    public function boot() {
        $modules = array();
        
        if($this->loadModules) {
            $modules = $this->loadModulesViaDoctrine();
        }
        
        $this->moduleBundles = array();
        foreach($modules as $module) {
            $class = $module->getName() . '\\' . $module->getName() . 'Module';
            $this->moduleBundles[] = new $class();
        }
        
        parent::boot();
    }
    
    public function registerModuleBundles(&$bundles) {
        if($this->moduleBundles) {
            foreach($this->moduleBundles as $bundle) {
                $bundles[] = $bundle;
            }
        }
    }
    
    public function getModuleBundles()
    {
        return $this->moduleBundles;
    }
    
    /**
     * Load active modules from DB.
     * 
     * We have to setup our own doctrine entity manager because the 
     * dependency injection container is not available in this stage.
     */
    private function loadModulesViaDoctrine() {
        $config = new \Doctrine\ORM\Configuration();
        
        $anoReader = new \Doctrine\Common\Annotations\AnnotationReader();
        $reader = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($anoReader, array(__DIR__ . '/Entity'));
        
        $config->setMetadataDriverImpl($reader);
        $config->setAutoGenerateProxyClasses(false);
        $config->setProxyDir(__DIR__.'/../../../app/cache/proxy');
        $config->setProxyNamespace('Proxy\Doctrine');
        
        $dbparams = parse_ini_file(__DIR__ . '/../../../app/config/parameters.ini');
        $em = \Doctrine\ORM\EntityManager::create(array('driver' => $dbparams['database_driver'], 
                                                        'host'   => $dbparams['database_host'],
                                                        'user'   => $dbparams['database_user'],
                                                        'password' => $dbparams['database_password'],
                                                        'dbname' => $dbparams['database_name'],
                                                        'port' => $dbparams['database_port']), 
                                                  $config);
        
        
        return $em->getRepository('Zikula\ModulesBundle\Entity\Module')->findBy(array('state' => 1));
    }

    public function getBundle($name, $first = true)
    {
        $bundleProviders = $this->container->get('zikula.runtimebundleproviders');
        
        if(isset($this->runtimeBundleMap[$name])) {
            return array($this->runtimeBundleMap[$name]);
        } else if($bundleProviders->hasBundle($name)) {
            return array($bundleProviders->getBundle($name));
        } else {
            return parent::getBundle($name, $first);
        }
    }
}
