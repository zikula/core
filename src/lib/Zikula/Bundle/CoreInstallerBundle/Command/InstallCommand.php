<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Install Zikula from the command line.')
            ->setHelp(<<<EOT
<info>zikula:install</info> command installs zikula.
EOT
            )
            ->setName('zikula:install');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
