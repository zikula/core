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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;
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
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var MigrationHelper
     */
    private $migrationHelper;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

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
    private $selectedSettings = [
        'username',
        'password',
        'locale',
        'router:request_context:host',
        'router:request_context:scheme',
        'router:request_context:base_url'
    ];

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        ControllerHelper $controllerHelper,
        MigrationHelper $migrationHelper,
        LocaleApiInterface $localeApi,
        StageHelper $stageHelper,
        AjaxUpgraderStage $ajaxUpgraderStage,
        TranslatorInterface $translator,
        string $installed
    ) {
        $this->controllerHelper = $controllerHelper;
        $this->migrationHelper = $migrationHelper;
        $this->localeApi = $localeApi;
        $this->stageHelper = $stageHelper;
        $this->ajaxUpgraderStage = $ajaxUpgraderStage;
        $this->installed = $installed;
        parent::__construct($kernel, $translator);
    }

    protected function configure()
    {
        $this->setDescription('Upgrade Zikula from the command line.');

        foreach ($this->settings as $name => $setting) {
            if (!in_array($name, $this->selectedSettings, true)) {
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

            return 1;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title($this->translator->trans('Zikula Upgrader Script'));
        $io->section($this->translator->trans('*** UPGRADING TO ZIKULA CORE %version% ***', ['%version%' => ZikulaKernel::VERSION]));
        $io->text($this->translator->trans('Upgrading Zikula in %env% environment.', ['%env%' => $this->kernel->getEnvironment()]));

        $warnings = $this->controllerHelper->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);

            return 2;
        }

        $yamlManager = new YamlDumper($this->kernel->getProjectDir() . '/config', 'services_custom.yaml');
        // tell the core that we are upgrading
        $yamlManager->setParameter('upgrading', true);

        $this->migrateUsers($io, $output);

        // get the settings from user input
        $settings = $this->getHelper('form')->interactUsingForm(LocaleType::class, $input, $output, [
            'choices' => $this->localeApi->getSupportedLocaleNames(),
            'choice_loader' => null
        ]);

        $data = $this->getHelper('form')->interactUsingForm(LoginType::class, $input, $output);
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $settings = array_merge($settings, $data);

        $data = $this->getHelper('form')->interactUsingForm(RequestContextType::class, $input, $output);
        foreach ($data as $k => $v) {
            $newKey = str_replace(':', '.', $k);
            $data[$newKey] = $v;
            unset($data[$k]);
        }
        $settings = array_merge($settings, $data);

        $this->printSettings($settings, $io);
        $io->newLine();

        // write the parameters into config/services_custom.yaml
        $params = array_merge($yamlManager->getParameters(), $settings);
        $yamlManager->setParameters($params);

        // upgrade!
        $this->stageHelper->handleAjaxStage($this->ajaxUpgraderStage, $io);

        $io->success($this->translator->trans('UPGRADE COMPLETE!'));

        return 0;
    }

    private function migrateUsers(SymfonyStyle $io, OutputInterface $output): void
    {
        if (version_compare($this->installed, '2.0.0', '>=')) {
            return;
        }
        $count = $this->migrationHelper->countUnMigratedUsers();
        if ($count > 0) {
            $io->text($this->translator->trans('Beginning user migration...'));
            $userMigrationMaxuid = (int)$this->migrationHelper->getMaxUnMigratedUid();
            $progressBar = new ProgressBar($output, (int)ceil($count / MigrationHelper::BATCH_LIMIT));
            $progressBar->start();
            $lastUid = 0;
            do {
                $result = $this->migrationHelper->migrateUsers($lastUid);
                $lastUid = $result['lastUid'];
                $progressBar->advance();
            } while ($lastUid < $userMigrationMaxuid);
            $progressBar->finish();
            $io->success($this->translator->trans('User migration complete!'));
        } else {
            $io->text($this->translator->trans('There was no need to migrate any users.'));
        }
    }
}
