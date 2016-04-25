<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('doctrine.entitymanager', 'doctrine.orm.default_entity_manager');

        $container->setAlias('doctrine.annotationreader', 'annotation_reader');
        $container->setAlias('doctrine.annotation_reader', 'annotation_reader');

        $definition = new Definition('Doctrine\ORM\Mapping\Driver\AnnotationDriver', [new Reference('doctrine.annotation_reader')]);
        $container->setDefinition('doctrine.annotation_driver', $definition);

        $definition = new Definition('Doctrine\ORM\Mapping\Driver\DriverChain');
        $container->setDefinition('doctrine.driver_chain', $definition);

        $definition = new Definition('Zikula\Core\Doctrine\ExtensionsManager', [new Reference('doctrine.eventmanager'), new Reference('service_container')]);
        $container->setDefinition('doctrine_extensions', $definition);

        $container->setAlias('doctrine.event_manager', (string)$container->getDefinition("doctrine.dbal.default_connection")->getArgument(2));
        $container->setAlias('doctrine.eventmanager', 'doctrine.event_manager');

        // todo - migrate to XML
        $definition = new Definition("Zikula\\Core\\Doctrine\\StandardFields\\StandardFieldsListener");
        $container->setDefinition("doctrine_extensions.listener.standardfields", $definition);

        $types = ['Blameable', 'Loggable', 'SoftDeleteable', 'Uploadable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree', 'Sortable'];
        foreach ($types as $type) {
            $container->setAlias(strtolower("doctrine_extensions.listener.$type"), 'stof_doctrine_extensions.listener.' . strtolower($type));
        }
    }
}
