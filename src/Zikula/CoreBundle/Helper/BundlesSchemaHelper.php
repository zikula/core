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

namespace Zikula\Bundle\CoreBundle\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Composer\Scanner;

class BundlesSchemaHelper
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var Scanner
     */
    private $scanner;

    public function __construct(
        Connection $conn,
        TranslatorInterface $translator,
        $projectDir
    ) {
        $this->conn = $conn;
        $this->projectDir = $projectDir;
        $this->scanner = new Scanner();
        $this->scanner->setTranslator($translator);
    }

    public function load(): void
    {
        $this->verifySchema();
        $this->scanner->scan([$this->projectDir . '/src/extensions']);
        $this->sync($this->scanner->getExtensionsMetaData());
    }

    public function getScanner(): Scanner
    {
        return $this->scanner;
    }

    /**
     * Sync the filesystem scan and the bundles table.
     * This is a 'dumb' scan - there is no state management here.
     * State management occurs in extensions management and is checked in CoreBundle\Helper\PersistedBundleHelper.
     */
    private function sync(array $fileExtensions = []): void
    {
        // add what is in array but missing from db
        /** @var MetaData $metadata */
        foreach ($fileExtensions as $name => $metadata) {
            $qb = $this->conn->createQueryBuilder();
            $qb->select('b.id', 'b.bundlename', 'b.bundleclass', 'b.autoload', 'b.bundletype')
                ->from('bundles', 'b')
                ->where('b.bundlename = :name')
                ->setParameter('name', $name);
            $result = $qb->execute();
            $row = $result->fetch();
            if (!$row) {
                // bundle doesn't exist
                $this->insert($metadata);
            } elseif (($metadata->getClass() !== $row['bundleclass']) || (serialize($metadata->getAutoload()) !== $row['autoload'])) {
                // bundle json has been updated
                $updatedMeta = [
                    'bundleclass' => $metadata->getClass(),
                    'autoload' => serialize($metadata->getAutoload())
                ];
                $this->conn->update('bundles', $updatedMeta, ['id' => $row['id']]);
            }
        }

        // remove what is in db but missing from array
        $qb = $this->conn->createQueryBuilder();
        $qb->select('b.id', 'b.bundlename', 'b.bundleclass', 'b.autoload', 'b.bundletype')
            ->from('bundles', 'b');
        $res = $qb->execute();
        foreach ($res->fetchAll() as $row) {
            if (!array_key_exists($row['bundlename'], $fileExtensions)) {
                $this->removeById((int) $row['id']);
            }
        }
    }

    private function removeById(int $id): void
    {
        $this->conn->delete('bundles', ['id' => $id]);
    }

    private function insert(MetaData $metadata): void
    {
        $name = $metadata->getName();
        $autoload = serialize($metadata->getAutoload());
        $class = $metadata->getClass();
        switch ($metadata->getType()) {
            case 'zikula-module':
                $type = 'M';
                break;
            case 'zikula-theme':
                $type = 'T';
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unknown type %s', $metadata->getType()));
        }

        $this->conn->insert('bundles', [
            'bundlename'  => $name,
            'autoload'    => $autoload,
            'bundleclass' => $class,
            'bundletype'  => $type,
        ]);
    }

    private function verifySchema(): void
    {
        $schemaManager = $this->conn->getSchemaManager();
        if (true !== $schemaManager->tablesExist(['bundles'])) {
            $this->createSchema();
        }
    }

    private function createSchema(): void
    {
        $schema = $this->conn->getSchemaManager();
        $table = new Table('bundles');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('bundlename', 'string', ['length' => 100]);
        $table->addColumn('autoload', 'string', ['length' => 384]);
        $table->addColumn('bundleclass', 'string', ['length' => 100]);
        $table->addColumn('bundletype', 'string', ['length' => 2]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['bundlename']);
        $charset = $this->conn->getDriver() instanceof AbstractMySQLDriver ? 'utf8mb4' : 'utf8';
        $table->addOption('charset', $charset);
        $schema->createTable($table);
    }
}
