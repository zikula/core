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

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType;
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\PhpHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\StageHelper;
use Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade\AjaxUpgraderStage;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class UpgradeCommand extends AbstractCoreInstallerCommand
{
    protected static $defaultName = 'zikula:upgrade';

    /**
     * @var string
     */
    private $installed;

    /**
     * @var PhpHelper
     */
    private $phpHelper;

    /**
     * @var MigrationHelper
     */
    private $migrationHelper;

    /**
     * @var StageHelper
     */
    private $stageHelper;

    /**
     * @var AjaxUpgraderStage
     */
    private $ajaxUpgraderStage;

    /**
     * @var array
     */
    private $upgradeSettings = [
        'username',
        'password',
        'locale',
        'router:request_context:host',
        'router:request_context:scheme',
        'router:request_context:base_url',
        'transport'
    ];

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        PhpHelper $phpHelper,
        MigrationHelper $migrationHelper,
        LocaleApiInterface $localeApi,
        StageHelper $stageHelper,
        AjaxUpgraderStage $ajaxUpgraderStage,
        TranslatorInterface $translator,
        string $installed
    ) {
        $this->phpHelper = $phpHelper;
        $this->migrationHelper = $migrationHelper;
        $this->stageHelper = $stageHelper;
        $this->ajaxUpgraderStage = $ajaxUpgraderStage;
        $this->installed = $installed;
        parent::__construct($kernel, $translator, $localeApi);
    }

    protected function configure()
    {
        $this->setDescription('Upgrade Zikula from the command line.');

        foreach ($this->settings as $name => $setting) {
            if (!in_array($name, $this->upgradeSettings, true)) {
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (version_compare($this->installed, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<')) {
            $output->writeln($this->translator->trans('The currently installed version of Zikula (%currentVersion%) is too old. You must upgrade to version %minimumVersion% before you can use this upgrade.', ['%currentVersion%' => $this->installed, '%minimumVersion%' => UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION]));

            return Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);
        if ($input->isInteractive()) {
            $io->title($this->translator->trans('Zikula Upgrader Script'));
            $io->section($this->translator->trans('*** UPGRADING TO ZIKULA CORE %version% ***', ['%version%' => ZikulaKernel::VERSION]));
            $io->text($this->translator->trans('Upgrading Zikula in %env% environment.', ['%env%' => $this->kernel->getEnvironment()]));
        }

        $iniWarnings = $this->phpHelper->setUp();
        if (!empty($iniWarnings)) {
            $this->printWarnings($output, $iniWarnings);

            return Command::FAILURE;
        }

        $yamlManager = new YamlDumper($this->kernel->getProjectDir() . '/config', 'services_custom.yaml');
        $yamlManager->setParameter('upgrading', true); // tell the core that we are upgrading

        $this->migrateUsers($input, $output, $io);

        // get the settings from user input
        $settings = $this->doLocale($input, $output, $io);
        $settings = array_merge($settings, $this->doAdminLogin($input, $output, $io));
        if (false === $mailSettings = $this->doMailer($input, $output, $io)) {
            $io->error($this->translator->trans('Cannot write mailer DSN to %file% file.', ['%file%' => '/.env.local']));
        } else {
            $settings = array_merge($settings, $mailSettings);
        }
        $settings = array_merge($settings, $this->doRequestContext($input, $output, $io));

        if ($input->isInteractive()) {
            $this->printSettings($settings, $io);
            $io->newLine();
        }

        $params = array_merge($yamlManager->getParameters(), $settings);
        $yamlManager->setParameters($params);

        // upgrade!
        $this->stageHelper->handleAjaxStage($this->ajaxUpgraderStage, $io, $input->isInteractive());

        $io->success($this->translator->trans('Upgrade successful!'));

        return Command::SUCCESS;
    }

    private function migrateUsers(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        if (version_compare($this->installed, '2.0.0', '>=')) {
            return;
        }
        $count = $this->migrationHelper->countUnMigratedUsers();
        if ($count > 0) {
            if ($input->isInteractive()) {
                $io->text($this->translator->trans('Beginning user migration...'));
            }
            $userMigrationMaxuid = (int)$this->migrationHelper->getMaxUnMigratedUid();
            if ($input->isInteractive()) {
                $progressBar = new ProgressBar($output, (int) ceil($count / MigrationHelper::BATCH_LIMIT));
                $progressBar->start();
            }
            $lastUid = 0;
            do {
                $result = $this->migrationHelper->migrateUsers($lastUid);
                $lastUid = $result['lastUid'];
                if ($input->isInteractive()) {
                    $progressBar->advance();
                }
            } while ($lastUid < $userMigrationMaxuid);
            if ($input->isInteractive()) {
                $progressBar->finish();
                $io->success($this->translator->trans('User migration complete!'));
            }
        } else {
            if ($input->isInteractive()) {
                $io->text($this->translator->trans('There was no need to migrate any users.'));
            }
        }
    }

    private function doAdminLogin(InputInterface $input, OutputInterface $output, StyleInterface $io): array
    {
        if ($input->isInteractive()) {
            $io->newLine();
            $io->section($this->translator->trans('Admin Login'));
        }
        $data = $this->getHelper('form')->interactUsingForm(LoginType::class, $input, $output);
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }

        return $data;
    }
}
