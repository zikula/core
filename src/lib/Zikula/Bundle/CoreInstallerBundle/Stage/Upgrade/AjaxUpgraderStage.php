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
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class AjaxUpgraderStage implements StageInterface, InjectContainerInterface
{
    use TranslatorTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->setTranslator($container->get('translator.default'));
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
                AjaxInstallerStage::NAME => 'upgrademodules',
                AjaxInstallerStage::PRE => $this->__('Upgrade modules'),
                AjaxInstallerStage::DURING => $this->__('Upgrading modules'),
                AjaxInstallerStage::SUCCESS => $this->__('Modules upgraded'),
                AjaxInstallerStage::FAIL => $this->__('There was an error upgrading the modules')
            ],
            3 => [
                AjaxInstallerStage::NAME => 'installroutes',
                AjaxInstallerStage::PRE => $this->__('Install Zikula Routes Module'),
                AjaxInstallerStage::DURING => $this->__('Installing Zikula Routes Module'),
                AjaxInstallerStage::SUCCESS => $this->__('Zikula Routes Module installed'),
                AjaxInstallerStage::FAIL => $this->__('There was an error installing Zikula Routes Module')
            ],
            4 => [
                AjaxInstallerStage::NAME => 'regenthemes',
                AjaxInstallerStage::PRE => $this->__('Regenerate themes'),
                AjaxInstallerStage::DURING => $this->__('Regenerating themes'),
                AjaxInstallerStage::SUCCESS => $this->__('Themes regenerated'),
                AjaxInstallerStage::FAIL => $this->__('There was an error regenerating the themes')
            ],
            5 => [
                AjaxInstallerStage::NAME => 'from140to141',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.0', '%newVersion%' => '1.4.1']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.1']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.1']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.1'])
            ],
            6 => [
                AjaxInstallerStage::NAME => 'from141to142',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.1', '%newVersion%' => '1.4.2']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.2']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.2']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.2'])
            ],
            7 => [
                AjaxInstallerStage::NAME => 'from142to143',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.2', '%newVersion%' => '1.4.3']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.3']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.3']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.3'])
            ],
            8 => [
                AjaxInstallerStage::NAME => 'from143to144',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.3', '%newVersion%' => '1.4.4']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.4']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.4']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.4'])
            ],
            9 => [
                AjaxInstallerStage::NAME => 'from144to145',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.4', '%newVersion%' => '1.4.5']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.5']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.5']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.5'])
            ],
            10 => [
                AjaxInstallerStage::NAME => 'from145to146',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.5', '%newVersion%' => '1.4.6']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.6']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.6']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.6'])
            ],
            11 => [
                AjaxInstallerStage::NAME => 'from146to147',
                AjaxInstallerStage::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => '1.4.6', '%newVersion%' => '1.4.7']),
                AjaxInstallerStage::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => '1.4.7']),
                AjaxInstallerStage::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => '1.4.7']),
                AjaxInstallerStage::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => '1.4.7'])
            ],
            12 => [
                AjaxInstallerStage::NAME => 'finalizeparameters',
                AjaxInstallerStage::PRE => $this->__('Finalize parameters'),
                AjaxInstallerStage::DURING => $this->__('Finalizing parameters'),
                AjaxInstallerStage::SUCCESS => $this->__('Parameters finalized'),
                AjaxInstallerStage::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            13 => [
                AjaxInstallerStage::NAME => 'clearcaches',
                AjaxInstallerStage::PRE => $this->__('Clear caches'),
                AjaxInstallerStage::DURING => $this->__('Clearing caches'),
                AjaxInstallerStage::SUCCESS => $this->__('Caches cleared'),
                AjaxInstallerStage::FAIL => $this->__('There was an error clearing caches')
            ],
            14 => [
                AjaxInstallerStage::NAME => 'finish',
                AjaxInstallerStage::PRE => $this->__('Finish'),
                AjaxInstallerStage::DURING => $this->__('Finish'),
                AjaxInstallerStage::SUCCESS => $this->__('Finish'),
                AjaxInstallerStage::FAIL => $this->__('Finish')
            ]
        ]];
    }
}
