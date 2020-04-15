<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Doctrine\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;

class SchemaHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SchemaTool
     */
    private $tool;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
        $this->tool = new SchemaTool($em);
    }

    /**
     * Create tables from array of entity classes.
     *
     * @throws ToolsException
     */
    public function create(array $classes = []): void
    {
        $metaClasses = [];
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        $this->tool->createSchema($metaClasses);
    }

    /**
     * Drop tables from array of entity classes.
     */
    public function drop(array $classes = []): void
    {
        $metaClasses = [];
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        $this->tool->dropSchema($metaClasses);
    }

    /**
     * Update tables from array of entity classes.
     */
    public function update(array $classes = [], bool $saveMode = true): void
    {
        $metaClasses = [];
        foreach ($classes as $class) {
            $metaClasses[] = $this->entityManager->getClassMetadata($class);
        }
        $this->tool->updateSchema($metaClasses, $saveMode);
    }
}
