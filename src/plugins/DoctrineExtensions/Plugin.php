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
                     'description' => $this->__('Provides various Doctrine Extensions libraries'),
                     'version'     => '0.0.2'
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
        $autoloader->register('Gedmo', __DIR__ . '/lib/vendor/l3pp4rd/DoctrineExtensions/lib', '\\');
        $autoloader->register('DoctrineExtensions\\StandardFields', __DIR__ . '/lib', '\\');
        $autoloader->register('DoctrineExtensions', __DIR__ . '/lib/vendor/beberlei/DoctrineExtensions/lib', '\\');
        
        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('Gedmo', __DIR__ . '/lib/vendor/l3pp4rd/DoctrineExtensions/lib');
        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('DoctrineExtensions\\StandardFields', __DIR__ . '/lib');
        
        include 'ExtensionsManager.php';
        $definition = new Zikula_ServiceManager_Definition('SystemPlugins_DoctrineExtensions_ExtensionsManager', array(new Zikula_ServiceManager_Reference('doctrine.eventmanager'), new Zikula_ServiceManager_Reference('zikula.servicemanager')));
        $this->serviceManager->registerService('doctrine_extensions', $definition);

        $types = array('Loggable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree', 'Sortable');
        foreach ($types as $type) {
            // The listener for Translatable is incorrectly named TranslationListener
            if ($type != "Translatable") {
                $definition = new Zikula_ServiceManager_Definition("Gedmo\\$type\\{$type}Listener");
            } else {
                $definition = new Zikula_ServiceManager_Definition("Gedmo\\Translatable\\TranslationListener");
            }
            $this->serviceManager->registerService(strtolower("doctrine_extensions.listener.$type"), $definition);
        }
        
        $definition = new Zikula_ServiceManager_Definition("DoctrineExtensions\\StandardFields\\StandardFieldsListener");
        $this->serviceManager->registerService(strtolower("doctrine_extensions.listener.standardfields"), $definition);
    }
}
