<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
