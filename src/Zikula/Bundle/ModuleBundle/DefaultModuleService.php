<?php

namespace Zikula\ModuleBundle;

use Doctrine\ORM\EntityManager;

/**
 * Default module service implementation.
 */
class DefaultModuleService implements ModuleServiceInterface
{
    /**
     * @var ModuleService\StorageInterface
     */
    private $storage;
    
    /**
     * @var String 
     */
    private $dirWithModules;
    
    /**
     * @var EntityManager
     */
    private $em;
    
    public function __construct(ModuleService\StorageInterface $storage, EntityManager $em, $dirWithModules=null) 
    {
        $this->em = $em;
        $this->storage = $storage;
        $this->dirWithModules = __DIR__ . '/../../../modules/'; // default
        
        if(!empty($dirWithModules)) {
            $this->dirWithModules = $dirWithModules;
        }
    }
    
    public function regenerateModuleList() 
    {
        $dir = new \DirectoryIterator($this->dirWithModules);
        
        $modules = array();
        foreach($dir as $fileInfo) {
            /* @var $fileInfo \SplFileInfo */
            
            if($fileInfo->isDir() && !$fileInfo->isDot()) {
                $class = $fileInfo->getFilename() . '\\' . $fileInfo->getFilename() . 'Module';
                $module = new $class();
                
                if(!$module instanceof ZikulaModule) {
                    throw new Exception\InvalidModuleStructureException($fileInfo->getFilename());
                }
                
                $modules[] = $module;
            }
        }
        
        $modulesInDb = $this->storage->getAll();
        
        foreach($modules as $module) {
            $dbmodule = null;
            foreach($modulesInDb as $moduleInDb) {
                if($moduleInDb->getName() == $module->getName()) {
                    $dbmodule = $moduleInDb;
                }
            }
            
            if($dbmodule) {
                if($dbmodule->getVersion() != $module->getVersion()) {
                    $dbmodule->setState(Entity\Module::STATE_NEED_UPGRADE);
                }
                $dbmodule->setVersion($module->getVersion());
                $this->storage->update($dbmodule);
            } else {
                $dbmodule = new Entity\Module();
                $dbmodule->setName($module->getName());
                $dbmodule->setVersion($module->getVersion());
                $dbmodule->setState(Entity\Module::STATE_NEW);
                $this->storage->insert($dbmodule);
            }
        }
    }
    
    public function getAllModules()
    {
        return $this->storage->getAll();
    }
    
    public function getModule($id)
    {
        return $this->storage->get($id);
    }
    
    public function installModule($id)
    {
        $module = $this->getModule($id);
        
        if(!$module) {
            throw new \InvalidArgumentException(sprintf('No module with $id %s', $id));
        }
        
        $class = $module->getName() . '\\' . $module->getName() . 'Module';
        
        $moduleObject = new $class();
        $installer = $moduleObject->createInstaller();
        
        $entities = $installer->entitiesToInstall();
        foreach($entities as $key => $class) {
            //FIXME: can we use symfony DIC to get a metadatafactory instance?
            $entities[$key] = $this->em->getMetadataFactory()->getMetadataFor($class);
        }
        
        if(count($entities) > 0) {
            //TODO: move to own class or class attribute to remove hard coded dependency to SchemaTool
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
            $schemaTool->createSchema($entities);
        }
        
        $installer->install();
        
        $module->setState(Entity\Module::STATE_ACTIVE);
        $this->storage->update($module);
    }
}
