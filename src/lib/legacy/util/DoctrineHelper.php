<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $metaClasses = [];
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
        $metaClasses = [];
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
        $metaClasses = [];
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
