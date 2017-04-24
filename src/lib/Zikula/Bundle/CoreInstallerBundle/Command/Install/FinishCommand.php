<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
        $io = new SymfonyStyle($input, $output);
        $this->bootstrap(false);

        if (true === $this->getContainer()->getParameter('installed')) {
            $io->error($this->translator->__('Zikula already appears to be installed.'));

            return;
        }

        $io->section($this->translator->__('*** INSTALLING ***'));
        $env = $this->getContainer()->get('kernel')->getEnvironment();
        $io->comment($this->translator->__f('Configuring Zikula installation in %env% environment.', ['%env%' => $env]));

        // install!
        $ajaxInstallerStage = new AjaxInstallerStage($this->getContainer());
        $stages = $ajaxInstallerStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            $io->text($stage[AjaxInstallerStage::PRE]);
            $io->text("<fg=blue;options=bold>" . $stage[AjaxInstallerStage::DURING] . "</fg=blue;options=bold>");
            $status = $this->getContainer()->get('zikula_core_installer.controller.ajaxinstall')->commandLineAction($stage[AjaxInstallerStage::NAME]);
            if ($status) {
                $io->success($stage[AjaxInstallerStage::SUCCESS]);
            } else {
                $io->error($stage[AjaxInstallerStage::FAIL]);
            }
        }
        $io->success($this->translator->__('INSTALL COMPLETE!'));
    }
}
