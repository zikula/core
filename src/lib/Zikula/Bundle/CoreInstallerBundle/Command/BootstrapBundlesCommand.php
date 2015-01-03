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

namespace Zikula\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap;
use Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper;

/**
 * Class BootstrapBundlesCommand
 * @package Zikula\Bundle\CoreBundle\Command
 */
class BootstrapBundlesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Loads bundles into persistence')
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
        $helper = new BootstrapHelper($boot->getConnection($this->getContainer()->get('kernel')));

        if ($input->getArgument('create')) {
            $helper->createSchema();
        }

        $helper->load();
    }
}