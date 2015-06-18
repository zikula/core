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

namespace Zikula\Core\Doctrine\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class SchemaHelper
 * @package Zikula\Core\Doctrine\Helper
 */
class SchemaHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    private $tool;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
        $this->tool = new SchemaTool($em);
    }

    /**
     * create tables from array of entity classes
     * @param array $classes
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function create(array $classes)
    {
        $metaClasses = array();
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        try {
            $this->tool->createSchema($metaClasses);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * drop tables from array of entity classes
     * @param array $classes
     */
    public function drop(array $classes)
    {
        $metaClasses = array();
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        try {
            $this->tool->dropSchema($metaClasses);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * update tables from array of entity classes
     * @param array $classes
     * @param bool $saveMode
     */
    public function update(array $classes, $saveMode=true)
    {
        $metaClasses = array();
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        try {
            $this->tool->updateSchema($metaClasses, $saveMode);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
