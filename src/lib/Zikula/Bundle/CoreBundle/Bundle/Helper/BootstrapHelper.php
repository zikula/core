<?php


namespace Zikula\Bundle\CoreBundle\Bundle\Helper;

use Zikula\Bundle\CoreBundle\Bundle\Scanner;

class BootstrapHelper
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function load()
    {
        $scanner = new Scanner();
        $scanner->scan(array('system', 'modules', 'themes'), 4);

        $this->truncate();
        $this->insert($scanner->getModulesMetaData(), 'M');
        $this->insert($scanner->getThemesMetaData(), 'T');
        $this->insert($scanner->getPluginsMetaData(), 'P');
    }

    private function truncate()
    {
        $this->conn->executeQuery('DELETE FROM bundles');
    }

    private function insert($array, $type)
    {
        foreach ($array as $name => $module) {
            $name = $module->getName();
            $autoload = serialize($module->getAutoload());
            $class = $module->getClass();
            $this->conn->executeUpdate(
                "INSERT INTO bundles (id, name, autoload, class, bundletype) VALUES (NULL, :name, :autoload, :class, :type)",
                array(
                    'name'     => $name,
                    'autoload' => $autoload,
                    'class'    => $class,
                    'type'     => $type,
                )
            );
        }
    }

    public function createSchema()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bundles` (
                `id` int(4) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `autoload` varchar(256) NOT NULL,
                `class` varchar(100) NOT NULL,
                `bundletype` varchar(2) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=483 ;";
         $this->conn->executeUpdate($sql);
    }
}
