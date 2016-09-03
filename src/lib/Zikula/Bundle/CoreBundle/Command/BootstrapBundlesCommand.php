<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapBundlesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Loads bundles into persistences')
            ->setHelp(<<<'EOT'
The <info>scan:bundles</info> command loads bundle table.
EOT
            )
            ->setDefinition([
                new InputArgument('create', InputArgument::OPTIONAL, 'Create schema'),
            ])
            ->setName('bootstrap:bundles');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getContainer()->get('zikula_core.internal.bootstrap_helper');

        if ($input->getArgument('create')) {
            $helper->createSchema();
        }

        $helper->load();
    }
}
