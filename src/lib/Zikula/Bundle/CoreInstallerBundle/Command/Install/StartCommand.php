<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\SettingsModule\Api\LocaleApi;

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

        if (true === $this->getContainer()->getParameter('installed')) {
            $io->error($this->translator->__('Zikula already appears to be installed.'));

            return;
        }

        $controllerHelper = $this->getContainer()->get(ControllerHelper::class);

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

        if ($input->isInteractive()) {
            $env = $this->getContainer()->get('kernel')->getEnvironment();
            $io->comment($this->translator->__f('Configuring Zikula installation in %env% environment.', ['%env%' => $env]));
            $io->comment($this->translator->__f('Please follow the instructions to install Zikula %version%.', ['%version%' => ZikulaKernel::VERSION]));
        }

        // get the settings from user input
        $settings = $this->getHelper('form')->interactUsingForm(LocaleType::class, $input, $output, [
            'choices' => $this->getContainer()->get(LocaleApi::class)->getSupportedLocaleNames()
        ]);
        $data = $this->getHelper('form')->interactUsingForm(RequestContextType::class, $input, $output);
        foreach ($data as $k => $v) {
            $newKey = str_replace(':', '.', $k);
            $data[$newKey] = $v;
            unset($data[$k]);
        }
        $settings = array_merge($settings, $data);
        $data = $this->getHelper('form')->interactUsingForm(DbCredsType::class, $input, $output);
        $settings = array_merge($settings, $data);
        $data = $this->getHelper('form')->interactUsingForm(CreateAdminType::class, $input, $output);
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $settings = array_merge($settings, $data);

        if ($input->isInteractive()) {
            $io->success($this->translator->__('Configuration successful. Please verify your parameters below:'));
            $io->comment($this->translator->__('(Admin credentials have been encoded to make them json-safe.)'));
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
        $yamlManager = new YamlDumper($this->getContainer()->get('kernel')->getRootDir() . '/config', 'custom_parameters.yml', 'parameters.yml');
        $params = array_merge($yamlManager->getParameters(), $settings);
        if ('pdo_' !== mb_substr($params['database_driver'], 0, 4)) {
            $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in custom_parameters.yml
        }
        $yamlManager->setParameters($params);
        $this->getContainer()->get(CacheClearer::class)->clear('symfony.config');

        $io->success($this->translator->__('First stage of installation complete. Run `php bin/console zikula:install:finish` to complete the installation.'));
    }
}
