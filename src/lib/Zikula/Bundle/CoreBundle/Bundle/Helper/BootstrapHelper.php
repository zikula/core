<?php


namespace Zikula\Bundle\CoreBundle\Bundle\Helper;

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
        $scanner->scan(array('modules', 'themes'), 4);
        $array = array_merge($scanner->getModulesMetaData(), $scanner->getThemesMetaData());
        $array = array_merge($array, $scanner->getPluginsMetaData());
        $this->sync($array);
    }

    private function sync($array)
    {
        // add what is in array but missing from db
        foreach ($array as $name => $metadata) {
            $result = $this->conn->executeQuery("SELECT id, bundlename, bundleclass, autoload, bundletype, bundlestate FROM bundles WHERE bundlename = :name", array('name' => $name));
            $row = $result->fetch();
            if (!$row) {
                $this->insert($metadata);
            } elseif ($row['bundlestate'] === AbstractBundle::STATE_MISSING) {
                $this->updateState($row['id'], AbstractBundle::STATE_DISABLED);
            }
        }

        // remove/mark what is in db by missing from array
        // not sure if a MISSING state is valid here - and if files were restored, what state do we restore them to?
        $res = $this->conn->executeQuery('SELECT id, bundlename, bundleclass, autoload, bundletype, bundlestate FROM bundles');
        foreach ($res->fetchAll() as $row) {
            if (!in_array($row['bundlename'], array_keys($array))
                && $row['bundlestate'] !== AbstractBundle::STATE_MISSING )
            {
                $this->updateState($row['id'], AbstractBundle::STATE_MISSING);
                echo 'UPDATE bundles SET bundlestate = ' . AbstractBundle::STATE_MISSING . " WHERE id = $row[id]\n<br />";
            }
        }
    }

    private function updateState($id, $state = AbstractBundle::STATE_DISABLED)
    {
        $this->conn->executeQuery('UPDATE bundles SET bundlestate = :state WHERE id = :id',
            array(
                 'state' => $state,
                 'id'    => $id,
            ));
    }

    private function removeById($id)
    {
        $this->conn->executeQuery('DELETE FROM bundles WHERE id = :id', array('id' => $id));
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
        $this->conn->executeUpdate(
            "INSERT INTO bundles (id, bundlename, autoload, bundleclass, bundletype, bundlestate) VALUES (NULL, :name, :autoload, :class, :type, :state)",
            array(
                 'name'     => $name,
                 'autoload' => $autoload,
                 'class'    => $class,
                 'type'     => $type,
                 'state'    => AbstractBundle::STATE_ACTIVE, // todo - this has to be changed
            )
        );
    }

    public function createSchema()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bundles` (
                `id` int(4) NOT NULL AUTO_INCREMENT,
                `bundlename` varchar(100) NOT NULL,
                `autoload` varchar(256) NOT NULL,
                `bundleclass` varchar(100) NOT NULL,
                `bundletype` varchar(2) DEFAULT NULL,
                `bundlestate` varchar(2) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->conn->executeUpdate($sql);
    }
}
