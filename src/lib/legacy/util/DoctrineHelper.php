<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\EntityManager as EntityManager;
use Doctrine\ORM\Tools\SchemaTool as SchemaTool;

/**
 * Class DoctrineHelper
 * @deprecated remove at Core-2.0
 */
class DoctrineHelper
{
    public static function createSchema(EntityManager $em, array $classes)
    {
        $tool = new SchemaTool($em);
        $metaClasses = array();
        foreach ($classes as $class) {
            $metaClasses[] = $em->getClassMetadata($class);
        }
        try {
            $tool->createSchema($metaClasses);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public static function dropSchema(EntityManager $em, array $classes)
    {
        $tool = new SchemaTool($em);
        $metaClasses = array();
        foreach ($classes as $class) {
            $metaClasses[] = $em->getClassMetadata($class);
        }
        try {
            $tool->dropSchema($metaClasses);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public static function updateSchema(EntityManager $em, array $classes, $saveMode = true)
    {
        $tool = new SchemaTool($em);
        $metaClasses = array();
        foreach ($classes as $class) {
            $metaClasses[] = $em->getClassMetadata($class);
        }
        try {
            $tool->updateSchema($metaClasses, $saveMode);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
