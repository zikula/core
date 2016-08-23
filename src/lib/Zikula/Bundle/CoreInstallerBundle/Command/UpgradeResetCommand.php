<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;

class UpgradeResetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Reset the version.')
            ->setName('zikula:upgrade:reset-version');
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'The version to reset to.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        $yamlManager = new YamlDumper($this->getContainer()->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
        $yamlManager->setParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM, $version);
        $output->writeln(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM . 'parameter reset to ' . $version);
    }
}
