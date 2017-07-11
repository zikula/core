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
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class AjaxUpgraderStage implements StageInterface, InjectContainerInterface
{
    use TranslatorTrait;

    /**
     * @var string
     */
    private $oldVersion;

    public function __construct(ContainerInterface $container)
    {
        $this->setTranslator($container->get('translator.default'));
        $this->oldVersion = $container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'ajaxupgrader';
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Upgrade:ajaxupgrader.html.twig';
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return ['stages' => [
            1 => [
                AjaxInstallerStage::NAME => 'loginadmin',
                AjaxInstallerStage::PRE => $this->__('Login'),
                AjaxInstallerStage::DURING => $this->__('Logging in as admin'),
                AjaxInstallerStage::SUCCESS => $this->__('Logged in as admin'),
                AjaxInstallerStage::FAIL => $this->__('There was an error logging in as admin')
            ],
            2 => [
                AjaxInstallerStage::NAME => 'upgrade_event',
                AjaxInstallerStage::PRE => $this->__('Fire upgrade event'),
                AjaxInstallerStage::DURING => $this->__('Firing upgrade event'),
                AjaxInstallerStage::SUCCESS => $this->__('Fired upgrade event'),
                AjaxInstallerStage::FAIL => $this->__('There was an error firing the upgrade event')
            ],
            3 => [
                AjaxInstallerStage::NAME => 'upgrademodules',
                AjaxInstallerStage::PRE => $this->__('Upgrade modules'),
                AjaxInstallerStage::DURING => $this->__('Upgrading modules'),
                AjaxInstallerStage::SUCCESS => $this->__('Modules upgraded'),
                AjaxInstallerStage::FAIL => $this->__('There was an error upgrading the modules')
            ],
            4 => [
                AjaxInstallerStage::NAME => 'regenthemes',
                AjaxInstallerStage::PRE => $this->__('Regenerate themes'),
                AjaxInstallerStage::DURING => $this->__('Regenerating themes'),
                AjaxInstallerStage::SUCCESS => $this->__('Themes regenerated'),
                AjaxInstallerStage::FAIL => $this->__('There was an error regenerating the themes')
            ],
            5 => [
                AjaxInstallerStage::NAME => 'versionupgrade',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => $this->oldVersion, '%newVersion%' => ZikulaKernel::VERSION]),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION])
            ],
            6 => [
                AjaxInstallerStage::NAME => 'finalizeparameters',
                AjaxInstallerStage::PRE => $this->__('Finalize parameters'),
                AjaxInstallerStage::DURING => $this->__('Finalizing parameters'),
                AjaxInstallerStage::SUCCESS => $this->__('Parameters finalized'),
                AjaxInstallerStage::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            7 => [
                AjaxInstallerStage::NAME => 'clearcaches',
                AjaxInstallerStage::PRE => $this->__('Clear caches'),
                AjaxInstallerStage::DURING => $this->__('Clearing caches'),
                AjaxInstallerStage::SUCCESS => $this->__('Caches cleared'),
                AjaxInstallerStage::FAIL => $this->__('There was an error clearing caches')
            ],
            8 => [
                AjaxInstallerStage::NAME => 'finish',
                AjaxInstallerStage::PRE => $this->__('Finish'),
                AjaxInstallerStage::DURING => $this->__('Finish'),
                AjaxInstallerStage::SUCCESS => $this->__('Finish'),
                AjaxInstallerStage::FAIL => $this->__('Finish')
            ]
        ]];
    }
}
