<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

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
        $this->translator = $this->container->get('translator.default');
    }

    public function getName()
    {
        return 'notupgraded';
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Upgrade:notupgraded.html.twig';
    }

    public function isNecessary()
    {
        $currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare($currentVersion, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<=')) {
            throw new AbortStageException($this->translator->__f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', ['%1$s' => $currentVersion, '%2$s' => UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION]));
        }
        // make sure selected language is installed
        $DBLocale = $this->fetchDBLocale();
        if (!in_array($DBLocale, $this->container->get('zikula_settings_module.locale_api')->getSupportedLocales())) {
            $variableApi = $this->container->get('zikula_extensions_module.api.variable');
            $variableApi->set(VariableApi::CONFIG, 'language_i18n', 'en');
            $variableApi->set(VariableApi::CONFIG, 'language', 'eng');
            $variableApi->set(VariableApi::CONFIG, 'locale', 'en');
            \ZLanguage::setLocale('en'); // @deprecated remove at Core-2.0
        }

        return true;
    }

    public function getTemplateParams()
    {
        return [];
    }

    /**
     * @return string Locale code (e.g. `en`)
     * @throws AbortStageException
     */
    private function fetchDBLocale()
    {
        $conn = $this->container->get('doctrine')->getConnection();
        $serializedValue = $conn->fetchColumn("
            SELECT value
            FROM module_vars
            WHERE name = 'language_i18n'
            AND modname = 'ZConfig'
        ");
        if ($serializedValue) {
            return unserialize($serializedValue);
        }

        throw new AbortStageException($this->translator->__('The installed language could not be detected.'));
    }
}
