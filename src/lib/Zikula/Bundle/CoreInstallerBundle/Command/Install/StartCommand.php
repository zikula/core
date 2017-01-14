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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;

class StartCommand extends AbstractCoreInstallerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zikula:install:start')
            ->setDescription('call this command first')
        ;

        foreach ($this->settings as $name => $setting) {
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
        $io = new SymfonyStyle($input, $output);
        $io->title($this->translator->__('Zikula Installer Script'));

        $this->bootstrap();

        if ($this->getContainer()->getParameter('installed') == true) {
            $io->error($this->translator->__('Zikula already appears to be installed.'));

            return;
        }

        $controllerHelper = $this->getContainer()->get('zikula_core_installer.controller.helper');

        $warnings = $controllerHelper->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);

            return;
        }
        $checks = $controllerHelper->requirementsMet($this->getContainer());
        if (true !== $checks) {
            $this->printRequirementsWarnings($output, $checks);

            return;
        }

        if ($input->isInteractive()) {
            $env = $this->getContainer()->get('kernel')->getEnvironment();
            $io->comment($this->translator->__f('Configuring Zikula installation in %env% environment.', ['%env%' => $env]));
            $io->comment($this->translator->__f('Please follow the instructions to install Zikula %version%.', ['%version%' => ZikulaKernel::VERSION]));
        }

        // get the settings from user input
        $settings = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType', $input, $output, [
            'translator' => $this->translator,
            'choices' => $this->getContainer()->get('zikula_settings_module.locale_api')->getSupportedLocaleNames()
        ]);
        $data = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType', $input, $output, ['translator' => $this->translator]);
        foreach ($data as $k => $v) {
            $newKey = str_replace(':', '.', $k);
            $data[$newKey] = $v;
            unset($data[$k]);
        }
        $settings = array_merge($settings, $data);
        $data = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType', $input, $output, ['translator' => $this->translator]);
        $settings = array_merge($settings, $data);
        $data = $this->getHelper('form')->interactUsingForm('Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType', $input, $output, ['translator' => $this->translator]);
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $settings = array_merge($settings, $data);

        if ($input->isInteractive()) {
            $io->success($this->translator->__("Configuration successful. Please verify your parameters below:"));
            $io->comment($this->translator->__("(Admin credentials have been encoded to make them json-safe.)"));
        }

        $this->printSettings($settings, $io);
        $io->newLine();

        if ($input->isInteractive()) {
            $confirmation = $io->confirm($this->translator->__('Start installation?'), true);

            if (!$confirmation) {
                $io->error($this->translator->__('Installation aborted'));

                return;
            }
        }

        // write the parameters to personal_config.php and custom_parameters.yml
        $yamlManager = new YamlDumper($this->getContainer()->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml', 'parameters.yml');
        $params = array_merge($yamlManager->getParameters(), $settings);
        $dbh = new \PDO("$params[database_driver]:host=$params[database_host];dbname=$params[database_name]", $params['database_user'], $params['database_password']);
        $params['database_server_version'] = $dbh->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in custom_parameters.yml
        $yamlManager->setParameters($params);
        $this->getContainer()->get('zikula_core_installer.config.helper')->writeLegacyConfig($params);
        $this->getContainer()->get('zikula.cache_clearer')->clear('symfony.config');

        $io->success($this->translator->__('First stage of installation complete. Run `php app/console zikula:install:finish` to complete the installation.'));
    }

    private function printSettings($givenSettings, SymfonyStyle $io)
    {
        $rows = [];
        foreach ($givenSettings as $name => $givenSetting) {
            if (isset($this->settings[$name]['password']) && $this->settings[$name]['password']) {
                $givenSetting = str_repeat("*", strlen($givenSetting));
            }
            $rows[] = [$name, $givenSetting];
        }
        $io->table([$this->translator->__('Param'), $this->translator->__('Value')], $rows);
    }
}
