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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SettingsModule\Api\LocaleApi;

class NotUpgradedStage implements StageInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    public function getName(): string
    {
        return 'notupgraded';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Upgrade/notupgraded.html.twig';
    }

    public function isNecessary(): bool
    {
        $currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare($currentVersion, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<')) {
            throw new AbortStageException($this->translator->trans('The currently installed version of Zikula (%currentVersion%) is too old. You must upgrade to version %minimumVersion% before you can use this upgrade.', ['%currentVersion%' => $currentVersion, '%minimumVersion%' => UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION]));
        }
        // make sure selected language is installed
        $DBLocale = $this->fetchDBLocale();
        if (!in_array($DBLocale, $this->container->get(LocaleApi::class)->getSupportedLocales(), true)) {
            $variableApi = $this->container->get(VariableApi::class);
            $variableApi->set(VariableApi::CONFIG, 'locale', 'en');
        }

        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    /**
     * @return string Locale code (e.g. `en`)
     * @throws AbortStageException
     */
    private function fetchDBLocale(): string
    {
        $conn = $this->container->get('doctrine')->getConnection();
        $serializedValue = $conn->fetchColumn("
            SELECT value
            FROM module_vars
            WHERE name = 'locale'
            AND modname = 'ZConfig'
        ");
        if ($serializedValue) {
            return unserialize($serializedValue);
        }

        throw new AbortStageException($this->translator->trans('The installed language could not be detected.'));
    }
}
