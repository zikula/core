<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Bundle\CoreInstallerBundle\Controller\UpgraderController;

class NotUpgradedStage implements StageInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        if (version_compare(ZIKULACORE_CURRENT_INSTALLED_VERSION, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION, '<=')) {
            throw new AbortStageException(__f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', array(ZIKULACORE_CURRENT_INSTALLED_VERSION, UpgraderController::ZIKULACORE_MINIMUM_UPGRADE_VERSION)));
        }
        // make sure selected language is installed
        $DBLocale = $this->fetchDBLocale();
        if (!in_array($DBLocale, \ZLanguage::getInstalledLanguages())) {
            \System::setVar('language_i18n', 'en');
            \System::setVar('language', 'eng');
            \System::setVar('locale', 'en');
            \ZLanguage::setLocale('en');
        }

        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }

    /**
     * @return string Locale code (e.g. `en`)
     * @throws AbortStageException
     */
    private function fetchDBLocale()
    {
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $serializedValue = $conn->fetchColumn("
            SELECT value
            FROM module_vars
            WHERE name = 'language_i18n'
            AND modname = 'ZConfig'
        ");
        if ($serializedValue) {
            return unserialize($serializedValue);
        }

        throw new AbortStageException(__('The installed language could not be detected.'));
    }
}
