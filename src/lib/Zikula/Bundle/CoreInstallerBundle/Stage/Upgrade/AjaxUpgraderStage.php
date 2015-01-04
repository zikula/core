<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Zikula\Component\Wizard\StageInterface;

class AjaxUpgraderStage implements StageInterface
{
    const NAME = "name";
    const PRE = "pre";
    const DURING = "during";
    const SUCCESS = "success";
    const FAIL = "fail";

    public function getName()
    {
        return 'ajaxupgrader';
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Upgrade:ajaxupgrader.html.twig";
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return array('stages' => array(
            1 => array(
                self::NAME => "upgrademodules",
                self::PRE => __('Upgrade modules'),
                self::DURING => __('Upgrading modules'),
                self::SUCCESS => __('Modules upgraded'),
                self::FAIL => __('There was an error upgrading the modules')
            ),
            2 => array(
                self::NAME => "reloadroutes",
                self::PRE => __('Reload routes'),
                self::DURING => __('Reloading routes (takes longer...)'),
                self::SUCCESS => __('Routes reloaded'),
                self::FAIL => __('There was an error reloading the routes')
            ),
            3 => array(
                self::NAME => "regenthemes",
                self::PRE => __('Regenerate themes'),
                self::DURING => __('Regenerating themes'),
                self::SUCCESS => __('Themes regenerated'),
                self::FAIL => __('There was an error regenerating the themes')
            ),
            4 => array(
                self::NAME => "loginadmin",
                self::PRE => __('Login'),
                self::DURING => __('Logging in as admin'),
                self::SUCCESS => __('Logged in as admin'),
                self::FAIL => __('There was an error logging in as admin')
            ),
            5 => array(
                self::NAME => "finalizeparameters",
                self::PRE => __('Finalize parameters'),
                self::DURING => __('Finalizing parameters'),
                self::SUCCESS => __('Parameters finalized'),
                self::FAIL => __('There was an error finalizing the parameters')
            ),
        ));
    }
}