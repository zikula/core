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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\StageInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class NotUpgradedStage implements StageInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var string
     */
    private $installed;

    public function __construct(
        TranslatorInterface $translator,
        LocaleApiInterface $localeApi,
        VariableApiInterface $variableApi,
        string $installed
    ) {
        $this->translator = $translator;
        $this->localeApi = $localeApi;
        $this->variableApi = $variableApi;
        $this->installed = $installed;
    }

    public function getName(): string
    {
        return 'notupgraded';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Upgrade/notUpgraded.html.twig';
    }

    public function isNecessary(): bool
    {
        if (version_compare($this->installed, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<')) {
            throw new AbortStageException($this->translator->trans('The currently installed version of Zikula (%currentVersion%) is too old. You must upgrade to version %minimumVersion% before you can use this upgrade.', ['%currentVersion%' => $this->installed, '%minimumVersion%' => UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION]));
        }
        // make sure selected language is installed
        $DBLocale = $this->variableApi->get(VariableApi::CONFIG, 'locale', '');
        if (empty($DBLocale) || !in_array($DBLocale, $this->localeApi->getSupportedLocales(), true)) {
            $this->variableApi->set(VariableApi::CONFIG, 'locale', 'en');
        }

        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }
}
