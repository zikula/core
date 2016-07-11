<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

/**
 * Class MigrationUtil
 *
 * This class provides some helpers to transition older modules
 *
 * @deprecated remove at Core-2.0
 */
class MigrationUtil
{
    public static function loadModuleAnnotations($entityNamespace, $path)
    {
        /** @var $em EntityManager */
        $em = ServiceUtil::get('doctrine.orm.entity_manager');
        /** @var $ORMConfig Configuration */
        $ORMConfig = $em->getConfiguration();
        $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
            ServiceUtil::get('annotation_reader'),
            [$path]
        );
        $chain = $ORMConfig->getMetadataDriverImpl(); // driver chain
        $chain->addDriver($annotationDriver, $entityNamespace);
    }
}
