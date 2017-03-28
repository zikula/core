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

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Event handler to set the default driver in the driver chain
 */
class DoctrineListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MappingDriver
     */
    private $annotationDriver;

    public function __construct(EntityManagerInterface $entityManager, MappingDriver $driver)
    {
        $this->entityManager = $entityManager;
        $this->annotationDriver = $driver;
    }

    public function setDefaultDriver(GetResponseEvent $event)
    {
        /** @var $ORMConfig Configuration */
        $ORMConfig = $this->entityManager->getConfiguration();
        $chain = $ORMConfig->getMetadataDriverImpl(); // driver chain
        $chain->setDefaultDriver($this->annotationDriver);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => [
                ['setDefaultDriver', 100]
            ]
        ];
    }
}
