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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade\AjaxUpgraderStage;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade\InitStage;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreBundle\YamlDumper;

class UpgradeCommand extends AbstractCoreInstallerCommand
{
    private $selectedSettings = array(
        'username',
        'password',
        'router.request_context.host',
        'router.request_context.scheme',
        'router.request_context.base_url'
    );

    protected function configure()
    {
        $this
            ->setDescription('Upgrade Zikula from the command line.')
            ->setName('zikula:upgrade');
        foreach ($this->settings as $name => $setting) {
            if (!in_array($name, $this->selectedSettings)) {
                // only use selected settings for upgrade
                continue;
            }
            $this->addOption(
                $name,
                null,
                InputOption::VALUE_REQUIRED,
                $setting['description'],
                $setting['default']
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (version_compare(ZIKULACORE_CURRENT_INSTALLED_VERSION, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<')) {
            $output->writeln(__f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', array(ZIKULACORE_CURRENT_INSTALLED_VERSION, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION)));
            return false;
        }

        $output->writeln(array(
            "<info>---------------------------</info>",
            "| Zikula Upgrader Script |",
            "<info>---------------------------</info>"
        ));
        $output->writeln("*** UPGRADING TO ZIKULA CORE v" . \Zikula_Core::VERSION_NUM . " ***");

        $this->bootstrap(false);

        $initStage = new InitStage($this->getContainer());
        $initStage->isNecessary(); // runs init and upgradeUsersModule methods and intentionally returns false

        $warnings = $this->getContainer()->get('core_installer.controller.util')->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);
            return;
        }
        $checks = $this->getContainer()->get('core_installer.controller.util')->requirementsMet($this->getContainer());
        if (true !== $checks) {
            $this->printRequirementsWarnings($output, $checks);
            return;
        }

        // get the settings from user input
        $settings = array();
        foreach ($this->selectedSettings as $name) {
            $settings[$name] = $this->getRequiredOption($input, $output, $name);
        }

        // write the parameters to custom_parameters.yml
        $yamlManager = new YamlDumper($this->getContainer()->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
        $params = array_merge($yamlManager->getParameters(), $settings);
        $yamlManager->setParameters($params);

        // upgrade!
        $ajaxInstallerStage = new AjaxUpgraderStage();
        $stages = $ajaxInstallerStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            $output->writeln($stage[AjaxInstallerStage::PRE]);
            $status = $this->getContainer()->get('core_installer.controller.ajaxupgrade')->commandLineAction($stage[AjaxInstallerStage::NAME]);
            $message = $status ? "<info>" . $stage[AjaxInstallerStage::SUCCESS] . "</info>" : "<error>" . $stage[AjaxInstallerStage::FAIL] . "</error>";
            $output->writeln($message);
        }

        $output->writeln("UPGRADE COMPLETE!");
    }

}
