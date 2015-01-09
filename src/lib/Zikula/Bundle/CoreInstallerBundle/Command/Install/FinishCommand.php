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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;
use Zikula_Request_Http as Request;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FinishCommand extends ContainerAwareCommand
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
        $this->bootstrap();
        $output->writeln("*** INSTALLING ***");
        // install!
        $ajaxInstallerStage = new AjaxInstallerStage();
        $stages = $ajaxInstallerStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            $output->writeln($stage[AjaxInstallerStage::PRE]);
            $status = $this->getContainer()->get('core_installer.controller.ajaxinstall')->commandLineAction($stage[AjaxInstallerStage::NAME]);
            $output->writeln($stage[$status ? AjaxInstallerStage::SUCCESS : AjaxInstallerStage::FAIL]);
        }
    }

    private function bootstrap()
    {
        $kernel = $this->getContainer()->get('kernel');
        $loader = require($kernel->getRootDir() . '/autoload.php');
        \ZLoader::register($loader);
        define('_ZINSTALLVER', \Zikula_Core::VERSION_NUM);

        // Fake request
        $request = Request::create('http://localhost/install');
        $this->getContainer()->set('request', $request);
    }
}
