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
 * DoctrineExtensions plugin definition.
 */
class SystemPlugin_DoctrineExtensions_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Doctrine Extensions'),
                     'description' => $this->__('Provides Gedmo DoctrineExtensions libraries'),
                     'version'     => '0.0.1-master'
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
        $autoloader = new Zikula_KernelClassLoader();
        $autoloader->spl_autoload_register();
        $autoloader->register('Gedmo', dirname(__FILE__) . '/lib', '\\');
        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('Gedmo', dirname(__FILE__) . '/lib');
        include 'ExtensionsManager.php';
        $definition = new Zikula_ServiceManager_Definition('SystemPlugins_DoctrineExtensions_ExtensionsManager', array(new Zikula_ServiceManager_Reference('doctrine.eventmanager'), new Zikula_ServiceManager_Reference('zikula.servicemanager')));
        $this->serviceManager->registerService('doctrine_extensions', $definition);

        $types = array('Loggable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree', 'Sortable');
        foreach ($types as $type) {
            $definition = new Zikula_ServiceManager_Definition("Gedmo\\$type\\{$type}Listener");
            $this->serviceManager->registerService(strtolower("doctrine_extensions.listener.$type"), $definition);
        }
    }
}
