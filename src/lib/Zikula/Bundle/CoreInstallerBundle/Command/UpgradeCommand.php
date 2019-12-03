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

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;
use Zikula\Bundle\CoreInstallerBundle\Manager\StageManager;
use Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade\AjaxUpgraderStage;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class UpgradeCommand extends AbstractCoreInstallerCommand
{
    /**
     * @var string
     */
    private $currentInstalledVersion;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

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
     * @var StageManager
     */
    private $stageManager;

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

    public function __construct(
        string $currentInstalledVersion,
        ZikulaHttpKernelInterface $kernel,
        ControllerHelper $controllerHelper,
        MigrationHelper $migrationHelper,
        LocaleApiInterface $localeApi,
        StageManager $stageManager,
        TranslatorInterface $translator
    ) {
        $this->currentInstalledVersion = $currentInstalledVersion;
        $this->kernel = $kernel;
        $this->controllerHelper = $controllerHelper;
        $this->migrationHelper = $migrationHelper;
        $this->localeApi = $localeApi;
        $this->stageManager = $stageManager;
        parent::__construct($translator);
    }

    protected function configure()
    {
        $this
            ->setDescription('Upgrade Zikula from the command line.')
            ->setName('zikula:upgrade')
        ;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (version_compare($this->currentInstalledVersion, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<')) {
            $output->writeln($this->translator->__f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', ['%1$s' => $this->currentInstalledVersion, '%2$s' => UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION]));

            return false;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title($this->translator->__('Zikula Upgrader Script'));
        $io->section($this->translator->__f('*** UPGRADING TO ZIKULA CORE %version% ***', ['%version%' => ZikulaKernel::VERSION]));
        $io->text($this->translator->__f('Upgrading Zikula in %env% environment.', ['%env%' => $this->kernel->getEnvironment()]));

        $warnings = $this->controllerHelper->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);

            return false;
        }
        $checks = $this->controllerHelper->requirementsMet();
        if (true !== $checks) {
            $this->printRequirementsWarnings($output, $checks);

            return false;
        }

        $count = $this->migrationHelper->countUnMigratedUsers();
        if ($count > 0) {
            $io->text($this->translator->__('Beginning user migration...'));
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
            $io->success($this->translator->__('User migration complete!'));
        } else {
            $io->text($this->translator->__('There was no need to migrate any users.'));
        }

        // avoid warning in PHP 7.2 based on ini_set() usage which is caused by any access to the
        // session before regeneration happens (e.g. by an event listener executed before a login)
        // see issue #3898 for the details
        $reportingLevel = error_reporting();
        error_reporting($reportingLevel & ~E_WARNING);

        // get the settings from user input
        $settings = $this->getHelper('form')->interactUsingForm(LocaleType::class, $input, $output, [
            'choices' => $this->localeApi->getSupportedLocaleNames()
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

        // write the parameters to custom_parameters.yml
        $yamlManager = new YamlDumper($this->kernel->getProjectDir() . '/app/config', 'custom_parameters.yml');
        $params = array_merge($yamlManager->getParameters(), $settings);
        unset($params['upgrading']);
        $yamlManager->setParameters($params);

        // upgrade!
        $ajaxStage = new AjaxUpgraderStage($this->translator, $this->currentInstalledVersion);
        $this->stageManager->handleAjaxStage($ajaxStage, $io);

        error_reporting($reportingLevel);

        $io->success($this->translator->__('UPGRADE COMPLETE!'));

        return true;
    }
}
