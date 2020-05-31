<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Bundle\CoreInstallerBundle\Helper\DbCredsHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\ParameterHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\PhpHelper;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class StartCommand extends AbstractCoreInstallerCommand
{
    protected static $defaultName = 'zikula:install:start';

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var PhpHelper
     */
    private $phpHelper;

    /**
     * @var ParameterHelper
     */
    private $parameterHelper;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        string $installed,
        PhpHelper $phpHelper,
        LocaleApiInterface $localeApi,
        ParameterHelper $parameterHelper,
        TranslatorInterface $translator
    ) {
        $this->kernel = $kernel;
        $this->installed = '0.0.0' !== $installed;
        $this->phpHelper = $phpHelper;
        $this->parameterHelper = $parameterHelper;
        parent::__construct($kernel, $translator, $localeApi);
    }

    protected function configure()
    {
        $this->setDescription('call this command first');

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->translator->trans('Zikula Installer Script'));

        if (true === $this->installed) {
            $io->error($this->translator->trans('Zikula already appears to be installed.'));

            return Command::FAILURE;
        }

        $iniWarnings = $this->phpHelper->setUp();
        if (!empty($iniWarnings)) {
            $this->printWarnings($output, $iniWarnings);

            return Command::FAILURE;
        }

        if ($input->isInteractive()) {
            $io->comment($this->translator->trans('Configuring Zikula installation in %env% environment.', ['%env%' => $this->kernel->getEnvironment()]));
            $io->comment($this->translator->trans('Please follow the instructions to install Zikula %version%.', ['%version%' => ZikulaKernel::VERSION]));
        }

        // get the settings from user input
        $settings = $this->doLocale($input, $output, $io);
        $settings = array_merge($settings, $this->doRequestContext($input, $output, $io));
        if (!$this->doDBCreds($input, $output, $io)) {
            $io->error($this->translator->trans('Cannot write database DSN to %file% file.', ['%file%' => '/.env.local']));
        }
        if (false === $mailSettings = $this->doMailer($input, $output, $io)) {
            $io->error($this->translator->trans('Cannot write mailer DSN to %file% file.', ['%file%' => '/.env.local']));
        } else {
            $settings = array_merge($settings, $mailSettings);
        }
        $settings = array_merge($settings, $this->doAdminCreate($input, $output, $io));

        if ($input->isInteractive()) {
            $io->success($this->translator->trans('Configuration successful. Please verify your parameters below:'));
            $io->comment($this->translator->trans('(Admin credentials have been encoded to make them json-safe.)'));
        }

        if ($input->isInteractive()) {
            $this->printSettings($settings, $io);
            $io->newLine();
            $confirmation = $io->confirm($this->translator->trans('Start installation?'), true);

            if (!$confirmation) {
                $io->error($this->translator->trans('Installation aborted'));

                return Command::FAILURE;
            }
        }

        // write parameters into config/services_custom.yaml and env vars into .env.local
        $this->parameterHelper->initializeParameters($settings);

        $io->success($this->translator->trans('First stage of installation complete. Run `php bin/console zikula:install:finish` to complete the installation.'));

        return Command::SUCCESS;
    }

    private function doDBCreds(InputInterface $input, OutputInterface $output, StyleInterface $io): bool
    {
        if ($input->isInteractive()) {
            $io->newLine();
            $io->section($this->translator->trans('Database information'));
            $io->note($this->translator->trans('The database port can be left empty.'));
        }
        $data = $this->getHelper('form')->interactUsingForm(DbCredsType::class, $input, $output);

        return (new DbCredsHelper($this->kernel->getProjectDir()))->writeDatabaseDsn($data);
    }

    private function doAdminCreate(InputInterface $input, OutputInterface $output, StyleInterface $io): array
    {
        if ($input->isInteractive()) {
            $io->newLine();
            $io->section($this->translator->trans('Create admin account'));
        }
        $data = $this->getHelper('form')->interactUsingForm(CreateAdminType::class, $input, $output);

        return $this->encodeArrayValues($data);
    }
}
