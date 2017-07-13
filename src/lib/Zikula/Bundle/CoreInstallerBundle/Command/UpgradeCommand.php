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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;
use Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade\AjaxUpgraderStage;

class UpgradeCommand extends AbstractCoreInstallerCommand
{
    /**
     * @var array
     */
    private $selectedSettings = [
        'username',
        'password',
        'router:request_context:host',
        'router:request_context:scheme',
        'router:request_context:base_url'
    ];

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
        $currentVersion = $this->getContainer()->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare($currentVersion, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<')) {
            $output->writeln($this->translator->__f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', ['%1$s' => $currentVersion, '%2$s' => UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION]));

            return false;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title($this->translator->__('Zikula Upgrader Script'));
        $io->section($this->translator->__f('*** UPGRADING TO ZIKULA CORE %version% ***', ['%version%' => ZikulaKernel::VERSION]));
        $env = $this->getContainer()->get('kernel')->getEnvironment();
        $io->text($this->translator->__f('Upgrading Zikula in %env% environment.', ['%env%' => $env]));

        $this->bootstrap(false);

        $controllerHelper = $this->getContainer()->get('zikula_core_installer.controller.helper');

        $warnings = $controllerHelper->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);

            return;
        }
        $checks = $controllerHelper->requirementsMet();
        if (true !== $checks) {
            $this->printRequirementsWarnings($output, $checks);

            return;
        }

        $migrationHelper = $this->getContainer()->get('zikula_core_installer.helper.migration_helper');
        $count = $migrationHelper->countUnMigratedUsers();
        if ($count > 0) {
            $io->text($this->translator->__('Beginning user migration...'));
            $userMigrationMaxuid = (int)$migrationHelper->getMaxUnMigratedUid();
            $progressBar = new ProgressBar($output, ceil($count / MigrationHelper::BATCH_LIMIT));
            $progressBar->start();
            $lastUid = 0;
            do {
                $result = $migrationHelper->migrateUsers($lastUid);
                $lastUid = $result['lastUid'];
                $progressBar->advance();
            } while ($lastUid < $userMigrationMaxuid);
            $progressBar->finish();
            $io->success($this->translator->__('User migration complete!'));
        } else {
            $io->text($this->translator->__('There was no need to migrate any users.'));
        }

        // get the settings from user input
        $settings = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType', $input, $output, [
            'translator' => $this->translator,
            'choices' => $this->getContainer()->get('zikula_settings_module.locale_api')->getSupportedLocaleNames()
        ]);
        $data = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType', $input, $output, ['translator' => $this->translator]);
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $settings = array_merge($settings, $data);
        $data = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType', $input, $output, ['translator' => $this->translator]);
        foreach ($data as $k => $v) {
            $newKey = str_replace(':', '.', $k);
            $data[$newKey] = $v;
            unset($data[$k]);
        }
        $settings = array_merge($settings, $data);
        $this->printSettings($settings, $io);
        $io->newLine();

        // write the parameters to custom_parameters.yml
        $yamlManager = new YamlDumper($this->getContainer()->get('kernel')->getRootDir() . '/config', 'custom_parameters.yml');
        $params = array_merge($yamlManager->getParameters(), $settings);
        unset($params['upgrading']);
        $yamlManager->setParameters($params);

        // upgrade!
        $ajaxInstallerStage = new AjaxUpgraderStage($this->getContainer());
        $stages = $ajaxInstallerStage->getTemplateParams();
        foreach ($stages['stages'] as $key => $stage) {
            $io->text($stage[AjaxInstallerStage::PRE]);
            $io->text("<fg=blue;options=bold>" . $stage[AjaxInstallerStage::DURING] . "</fg=blue;options=bold>");
            $status = $this->getContainer()->get('zikula_core_installer.controller.ajaxupgrade')->commandLineAction($stage[AjaxInstallerStage::NAME]);
            if ($status) {
                $io->success($stage[AjaxInstallerStage::SUCCESS]);
            } else {
                $io->error($stage[AjaxInstallerStage::FAIL]);
            }
        }

        $io->success($this->translator->__('UPGRADE COMPLETE!'));
    }
}
