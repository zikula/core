<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Doctrine\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class SchemaHelper
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
        $metaClasses = [];
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        $this->tool->createSchema($metaClasses);
    }

    /**
     * drop tables from array of entity classes
     * @param array $classes
     */
    public function drop(array $classes)
    {
        $metaClasses = [];
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        $this->tool->dropSchema($metaClasses);
    }

    /**
     * update tables from array of entity classes
     * @param array $classes
     * @param bool $saveMode
     */
    public function update(array $classes, $saveMode = true)
    {
        $metaClasses = [];
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        $this->tool->updateSchema($metaClasses, $saveMode);
    }
}
