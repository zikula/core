<?php


namespace Zikula\Bundle\CoreBundle\Bundle\Helper;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\IntegerType;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Doctrine\DBAL\Connection;
use Zikula\Core\AbstractBundle;

class BootstrapHelper
{
    /**
     * @var Connection
     */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function load()
    {
        $scanner = new Scanner();
        $scanner->scan(array('modules', 'themes'), 5);
        $array = array_merge($scanner->getModulesMetaData(), $scanner->getThemesMetaData());
        $array = array_merge($array, $scanner->getPluginsMetaData());
        $this->sync($array);
    }

    /**
     * Sync the filesystem scan and the Bundles table
     * This is a 'dumb' scan - there is no state management here
     *      state management occurs in the module and theme management
     *      and is checked in Bundle/Bootstrap
     *
     * @param $array array of extensions
     *          obtained from filesystem scan
     *          key is bundle name and value an instance of \Zikula\Bundle\CoreBundle\Bundle\MetaData
     */
    private function sync($array)
    {
        // add what is in array but missing from db
        /** @var $metadata MetaData */
        foreach ($array as $name => $metadata) {
            $qb = $this->conn->createQueryBuilder();
            $qb->select('b.id', 'b.bundlename', 'b.bundleclass', 'b.autoload', 'b.bundletype', 'b.bundlestate')
                ->from('bundles', 'b')
                ->where('b.bundlename = :name')
                ->setParameter('name', $name);
            $result = $qb->execute();
            $row = $result->fetch();
            if (!$row) {
                // bundle doesn't exist
                $this->insert($metadata);
            } elseif (($metadata->getClass() != $row['bundleclass']) || (serialize($metadata->getAutoload()) != $row['autoload'])) {
                // bundle json has been updated
                $updatedMeta = array("bundleclass" => $metadata->getClass(), "autoload" => serialize($metadata->getAutoload()));
                $this->conn->update('bundles', $updatedMeta, array('id' => $row['id']));
            }
        }

        // remove what is in db but missing from array
        $qb = $this->conn->createQueryBuilder();
        $qb->select('b.id', 'b.bundlename', 'b.bundleclass', 'b.autoload', 'b.bundletype', 'b.bundlestate')
            ->from('bundles', 'b');
        $res = $qb->execute();
        foreach ($res->fetchAll() as $row) {
            if (!in_array($row['bundlename'], array_keys($array))) {
                    $this->removeById($row['id']);
            }
        }

        // clear the cache
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = \ServiceUtil::getManager()->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');
    }

    private function updateState($id, $state = AbstractBundle::STATE_DISABLED)
    {
        $this->conn->update('bundles', array('bundlestate' => $state),
                                       array('id' => $id)
        );
    }

    private function removeById($id)
    {
        $this->conn->delete('bundles', array('id' => $id));
    }

    private function truncate()
    {
        $this->conn->executeQuery('DELETE FROM bundles');
    }

    private function insert(MetaData $metadata)
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
            case 'zikula-plugin':
                $type = 'P';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown type %s', $metadata->getType()));
        }

        $this->conn->insert('bundles', array(
                 'bundlename'     => $name,
                 'autoload' => $autoload,
                 'bundleclass'    => $class,
                 'bundletype'     => $type,
                 'bundlestate'    => AbstractBundle::STATE_ACTIVE, // todo - this has to be changed
            )
        );
    }

    public function createSchema()
    {
        $schema = $this->conn->getSchemaManager();
        $table = new Table('bundles');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('bundlename', 'string', array('length' => 100));
        $table->addColumn('autoload', 'string', array('length' => 384));
        $table->addColumn('bundleclass', 'string', array('length' => 100));
        $table->addColumn('bundletype', 'string', array('length' => 2));
        $table->addColumn('bundlestate', 'integer', array('length' => 1));
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('bundlename'));
        $schema->createTable($table);
    }
}
