<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Doctrine\Listener\MySqlGenerateSchemaListener;
use Zikula\Core\Doctrine\Logger\ZikulaSqlLogger;
use Zikula\Core\Event\GenericEvent;

/**
 * Event handler to boot Doctrine 2
 */
class DoctrineListener implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public function initDoctrine(GenericEvent $event)
    {
        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var $ORMConfig Configuration */
        $ORMConfig = $em->getConfiguration();

        $chain = $ORMConfig->getMetadataDriverImpl(); // driver chain
        $defaultAnnotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($this->container->get('annotation_reader'));
        $chain->setDefaultDriver($defaultAnnotationDriver);

        if (isset($serviceManager['log.enabled']) && $serviceManager['log.enabled']) {
            $ORMConfig->setSQLLogger(new ZikulaSqlLogger());
        }

        // setup doctrine eventmanager
        $eventManager = $em->getEventManager();
        $this->container->set('doctrine.event_manager', $eventManager);

        $config = $GLOBALS['ZConfig']['DBInfo']['databases']['default'];
        // setup MySQL specific listener (storage engine and encoding)
        if ('mysql' == $config['dbdriver']) {
            $mysqlSessionInit = new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($config['charset']);
            $eventManager->addEventSubscriber($mysqlSessionInit);

            new MySqlGenerateSchemaListener($eventManager);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'doctrine.boot' => [
                ['initDoctrine', 100]
            ]
        ];
    }
}
