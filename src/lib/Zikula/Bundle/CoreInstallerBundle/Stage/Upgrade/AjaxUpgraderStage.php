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

use Zikula\Component\Wizard\StageInterface;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class AjaxUpgraderStage implements StageInterface
{
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
                AjaxInstallerStage::PRE => __('Login'),
                AjaxInstallerStage::DURING => __('Logging in as admin'),
                AjaxInstallerStage::SUCCESS => __('Logged in as admin'),
                AjaxInstallerStage::FAIL => __('There was an error logging in as admin')
            ],
            2 => [
                AjaxInstallerStage::NAME => 'upgrademodules',
                AjaxInstallerStage::PRE => __('Upgrade modules'),
                AjaxInstallerStage::DURING => __('Upgrading modules'),
                AjaxInstallerStage::SUCCESS => __('Modules upgraded'),
                AjaxInstallerStage::FAIL => __('There was an error upgrading the modules')
            ],
            3 => [
                AjaxInstallerStage::NAME => 'installroutes',
                AjaxInstallerStage::PRE => __('Install Zikula Routes Module'),
                AjaxInstallerStage::DURING => __('Installing Zikula Routes Module'),
                AjaxInstallerStage::SUCCESS => __('Zikula Routes Module installed'),
                AjaxInstallerStage::FAIL => __('There was an error installing Zikula Routes Module')
            ],
            4 => [
                AjaxInstallerStage::NAME => 'reloadroutes',
                AjaxInstallerStage::PRE => __('Reload routes'),
                AjaxInstallerStage::DURING => __('Reloading routes (takes longer...)'),
                AjaxInstallerStage::SUCCESS => __('Routes reloaded'),
                AjaxInstallerStage::FAIL => __('There was an error reloading the routes')
            ],
            5 => [
                AjaxInstallerStage::NAME => 'regenthemes',
                AjaxInstallerStage::PRE => __('Regenerate themes'),
                AjaxInstallerStage::DURING => __('Regenerating themes'),
                AjaxInstallerStage::SUCCESS => __('Themes regenerated'),
                AjaxInstallerStage::FAIL => __('There was an error regenerating the themes')
            ],
            6 => [
                AjaxInstallerStage::NAME => 'from140to141',
                AjaxInstallerStage::PRE => __('Upgrade from Core 1.4.0 to Core 1.4.1'),
                AjaxInstallerStage::DURING => __('Upgrading to Core 1.4.1'),
                AjaxInstallerStage::SUCCESS => __('Upgraded to Core 1.4.1'),
                AjaxInstallerStage::FAIL => __('There was an error upgrading to Core 1.4.1')
            ],
            7 => [
                AjaxInstallerStage::NAME => 'from141to142',
                AjaxInstallerStage::PRE => __('Upgrade from Core 1.4.1 to Core 1.4.2'),
                AjaxInstallerStage::DURING => __('Upgrading to Core 1.4.2'),
                AjaxInstallerStage::SUCCESS => __('Upgraded to Core 1.4.2'),
                AjaxInstallerStage::FAIL => __('There was an error upgrading to Core 1.4.2')
            ],
            8 => [
                AjaxInstallerStage::NAME => 'from142to143',
                AjaxInstallerStage::PRE => __('Upgrade from Core 1.4.2 to Core 1.4.3'),
                AjaxInstallerStage::DURING => __('Upgrading to Core 1.4.3'),
                AjaxInstallerStage::SUCCESS => __('Upgraded to Core 1.4.3'),
                AjaxInstallerStage::FAIL => __('There was an error upgrading to Core 1.4.3')
            ],
            9 => [
                AjaxInstallerStage::NAME => 'finalizeparameters',
                AjaxInstallerStage::PRE => __('Finalize parameters'),
                AjaxInstallerStage::DURING => __('Finalizing parameters'),
                AjaxInstallerStage::SUCCESS => __('Parameters finalized'),
                AjaxInstallerStage::FAIL => __('There was an error finalizing the parameters')
            ],
            10 => [
                AjaxInstallerStage::NAME => 'clearcaches',
                AjaxInstallerStage::PRE => __('Clear caches'),
                AjaxInstallerStage::DURING => __('Clearing caches'),
                AjaxInstallerStage::SUCCESS => __('Caches cleared'),
                AjaxInstallerStage::FAIL => __('There was an error clearing caches')
            ],
            11 => [
                AjaxInstallerStage::NAME => 'finish',
                AjaxInstallerStage::PRE => __('Finish'),
                AjaxInstallerStage::DURING => __('Finish'),
                AjaxInstallerStage::SUCCESS => __('Finish'),
                AjaxInstallerStage::FAIL => __('Finish')
            ]
        ]];
    }
}
