<?php

namespace Zikula\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;

class BootstrapBundlesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Loads bundles into persistences')
            ->setHelp(<<<EOT
The <info>scan:bundles</info> command loads bundle table.
EOT
            )
            ->setDefinition(array(
                new InputArgument('create', InputArgument::OPTIONAL, 'Create schema'),
            ))
            ->setName('bootstrap:bundles');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $boot = new Bootstrap();
        $conn = $boot->getConnection($this->getContainer()->get('kernel'));

        if ($input->getArgument('create')) {
            $this->createSchema($conn);
        }

        $conn->executeQuery('DELETE FROM bundles');

        $scanner = new Scanner();
        $scanner->scan(array('system', 'modules', 'themes'), 4);

        $this->insert($conn, $scanner->getModulesMetaData(), 'M');
        $this->insert($conn, $scanner->getThemesMetaData(), 'T');
        $this->insert($conn, $scanner->getPluginsMetaData(), 'P');
    }

    private function insert($conn, $array, $type)
    {
        foreach ($array as $name => $module) {
            $name = $module->getName();
            $autoload = serialize($module->getAutoload());
            $class = $module->getClass();
            $conn->executeUpdate(
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

    private function createSchema($conn)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bundles` (
                `id` int(4) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `autoload` varchar(256) NOT NULL,
                `class` varchar(100) NOT NULL,
                `bundletype` varchar(2) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=483 ;";
         $conn->executeUpdate($sql);
    }

}
