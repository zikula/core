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
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\DbCredsHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\ParameterHelper;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class StartCommand extends AbstractCoreInstallerCommand
{
    protected static $defaultName = 'zikula:install:start';

    /**
     * @var string
     */
    private $installed;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var ParameterHelper
     */
    private $parameterHelper;

    /**
     * @var string
     */
    private $localEnvFile;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        bool $installed,
        ControllerHelper $controllerHelper,
        LocaleApiInterface $localeApi,
        ParameterHelper $parameterHelper,
        TranslatorInterface $translator
    ) {
        $this->kernel = $kernel;
        $this->installed = $installed;
        $this->controllerHelper = $controllerHelper;
        $this->localeApi = $localeApi;
        $this->parameterHelper = $parameterHelper;
        $this->localEnvFile = $kernel->getProjectDir() . '/.env.local';
        parent::__construct($kernel, $translator);
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

            return 1;
        }

        $warnings = $this->controllerHelper->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);

            return 2;
        }
        $checks = $this->controllerHelper->requirementsMet();
        if (true !== $checks) {
            $this->printRequirementsWarnings($output, $checks);

            return 2;
        }

        if ($input->isInteractive()) {
            $io->comment($this->translator->trans('Configuring Zikula installation in %env% environment.', ['%env%' => $this->kernel->getEnvironment()]));
            $io->comment($this->translator->trans('Please follow the instructions to install Zikula %version%.', ['%version%' => ZikulaKernel::VERSION]));
        }

        // get the settings from user input
        $settings = $this->getHelper('form')->interactUsingForm(LocaleType::class, $input, $output, [
            'choices' => $this->localeApi->getSupportedLocaleNames(),
            'choice_loader' => null
        ]);
        $data = $this->getHelper('form')->interactUsingForm(RequestContextType::class, $input, $output);
        foreach ($data as $k => $v) {
            $newKey = str_replace(':', '.', $k);
            $data[$newKey] = $v;
            unset($data[$k]);
        }
        $settings = array_merge($settings, $data);
        $data = $this->getHelper('form')->interactUsingForm(DbCredsType::class, $input, $output);

        $dbCredsHelper = new DbCredsHelper();
        $databaseUrl = $dbCredsHelper->buildDatabaseUrl($data);
        $this->writeDatabaseUrl($io, $databaseUrl);

        $data = $this->getHelper('form')->interactUsingForm(CreateAdminType::class, $input, $output);
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $settings = array_merge($settings, $data);

        if ($input->isInteractive()) {
            $io->success($this->translator->trans('Configuration successful. Please verify your parameters below:'));
            $io->comment($this->translator->trans('(Admin credentials have been encoded to make them json-safe.)'));
        }

        $this->printSettings($settings, $io);
        $io->newLine();

        if ($input->isInteractive()) {
            $confirmation = $io->confirm($this->translator->trans('Start installation?'), true);

            if (!$confirmation) {
                $io->error($this->translator->trans('Installation aborted'));

                return 3;
            }
        }

        // write parameters into config/services_custom.yaml and env vars into .env.local
        $this->parameterHelper->initializeParameters($settings);

        $io->success($this->translator->trans('First stage of installation complete. Run `php bin/console zikula:install:finish` to complete the installation.'));

        return 0;
    }

    private function writeDatabaseUrl(SymfonyStyle $io, string $databaseUrl): void
    {
        // write env vars into .env.local
        $content = 'DATABASE_URL=\'' . $databaseUrl . "'\n";

        $fileSystem = new Filesystem();
        try {
            $fileSystem->dumpFile($this->localEnvFile, $content);
        } catch (IOExceptionInterface $exception) {
            $io->error(sprintf('Cannot write parameters to %s file.', $this->localEnvFile) . ' ' . $exception->getMessage());
        }
    }
}
