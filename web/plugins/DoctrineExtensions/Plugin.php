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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zikula\Framework\Plugin\AlwaysOnInterface;
use Zikula\Framework\AbstractPlugin;
use Symfony\Component\ClassLoader\UniversalClassLoader;

/**
 * DoctrineExtensions plugin definition.
 */
class SystemPlugin_DoctrineExtensions_Plugin extends AbstractPlugin implements AlwaysOnInterface
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
        $autoloader = new UniversalClassLoader();
        $autoloader->register();
        $autoloader->register('DoctrineExtensions\\StandardFields', __DIR__ . '/lib');
        $autoloader->register('DoctrineExtensions', __DIR__ . '/lib/vendor/beberlei/DoctrineExtensions/lib');

        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('Gedmo', dirname(__DIR__) . '/../vendor/gedmo/doctrine-extensions/lib');
        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('DoctrineExtensions\\StandardFields', __DIR__ . '/lib');

        include 'ExtensionsManager.php';
        $definition = new Definition('SystemPlugins_DoctrineExtensions_ExtensionsManager', array(new Reference('doctrine.eventmanager'), new Reference('service_container')));
        $this->container->setDefinition('doctrine_extensions', $definition);

        $types = array('Loggable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree', 'Sortable');
        foreach ($types as $type) {
            // The listener for Translatable is incorrectly named TranslationListener
            if ($type != "Translatable") {
                $definition = new Definition("Gedmo\\$type\\{$type}Listener");
            } else {
                $definition = new Definition("Gedmo\\Translatable\\TranslationListener");
            }
            $this->container->setDefinition(strtolower("doctrine_extensions.listener.$type"), $definition);
        }

        $definition = new Definition("DoctrineExtensions\\StandardFields\\StandardFieldsListener");
        $this->container->setDefinition(strtolower("doctrine_extensions.listener.standardfields"), $definition);
    }
}
