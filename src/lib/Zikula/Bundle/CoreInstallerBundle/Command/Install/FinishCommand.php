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

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class FinishCommand extends AbstractCoreInstallerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zikula:install:finish')
            ->setDescription('Call this command after zikula:install:start')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap(false);

        if ($this->getContainer()->getParameter('installed') == true) {
            $output->writeln("<error>" . __('Zikula already appears to be installed.') . "</error>");

            return;
        }

        $output->writeln("*** INSTALLING ***");
        $env = $this->getContainer()->get('kernel')->getEnvironment();
        $output->writeln('Configuring Zikula installation in <info>' . $env . '</info> environment.');

        // install!
        $ajaxInstallerStage = new AjaxInstallerStage();
        $stages = $ajaxInstallerStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            $output->writeln($stage[AjaxInstallerStage::PRE]);
            $output->writeln("<fg=blue;options=bold>" . $stage[AjaxInstallerStage::DURING] . "</fg=blue;options=bold>");
            $status = $this->getContainer()->get('core_installer.controller.ajaxinstall')->commandLineAction($stage[AjaxInstallerStage::NAME]);
            $message = $status ? "<info>" . $stage[AjaxInstallerStage::SUCCESS] . "</info>" : "<error>" . $stage[AjaxInstallerStage::FAIL] . "</error>";
            $output->writeln($message);
        }
    }
}
