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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Zikula\Component\Wizard\StageInterface;

class AjaxInstallerStage implements StageInterface
{
    const NAME = "name";
    const PRE = "pre";
    const DURING = "during";
    const SUCCESS = "success";
    const FAIL = "fail";

    public function getName()
    {
        return 'ajaxinstaller';
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:ajaxinstaller.html.twig";
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return array('stages' => array(
            1 => array(
                self::NAME => "bundles",
                self::PRE => __('Symfony Bundles'),
                self::DURING => __('Persisting Symfony Bundles'),
                self::SUCCESS => __('Symfony Bundles persisted'),
                self::FAIL => __('There was an error persisting the Symfony Bundles')
            ),
            2 => array(
                self::NAME => "install_event",
                self::PRE => __('Fire install event'),
                self::DURING => __('Firing install event'),
                self::SUCCESS => __('Fired install event'),
                self::FAIL => __('There was an error firing the install event')
            ),
            3 => array(
                self::NAME => "extensions",
                self::PRE => __('Zikula Extension Module'),
                self::DURING => __('Installing Zikula Extensions Module'),
                self::SUCCESS => __('Zikula Extensions Module installed'),
                self::FAIL => __('There was an error installing Zikula Extensions Module')
            ),
            4 => array(
                self::NAME => "settings",
                self::PRE => __('Zikula Settings Module'),
                self::DURING => __('Installing Zikula Settings Module'),
                self::SUCCESS => __('Zikula Settings Module installed'),
                self::FAIL => __('There was an error installing Zikula Settings Module')
            ),
            5 => array(
                self::NAME => "theme",
                self::PRE => __('Zikula Theme Module'),
                self::DURING => __('Installing Zikula Theme Module'),
                self::SUCCESS => __('Zikula Theme Module installed'),
                self::FAIL => __('There was an error installing Zikula Theme Module')
            ),
            6 => array(
                self::NAME => "admin",
                self::PRE => __('Zikula Administration Module'),
                self::DURING => __('Installing Zikula Administration Module'),
                self::SUCCESS => __('Zikula Administration Module installed'),
                self::FAIL => __('There was an error installing Zikula Administration Module')
            ),
            7 => array(
                self::NAME => "permissions",
                self::PRE => __('Zikula Permissions Module'),
                self::DURING => __('Installing Zikula Permissions Module'),
                self::SUCCESS => __('Zikula Permissions Module installed'),
                self::FAIL => __('There was an error installing Zikula Permissions Module')
            ),
            8 => array(
                self::NAME => "users",
                self::PRE => __('Zikula Users Module'),
                self::DURING => __('Installing Zikula Users Module'),
                self::SUCCESS => __('Zikula Users Module installed'),
                self::FAIL => __('There was an error installing Zikula Users Module')
            ),
            9 => array(
                self::NAME => "groups",
                self::PRE => __('Zikula Groups Module'),
                self::DURING => __('Installing Zikula Groups Module'),
                self::SUCCESS => __('Zikula Groups Module installed'),
                self::FAIL => __('There was an error installing Zikula Groups Module')
            ),
            10 => array(
                self::NAME => "blocks",
                self::PRE => __('Zikula Blocks Module'),
                self::DURING => __('Installing Zikula Blocks Module'),
                self::SUCCESS => __('Zikula Blocks Module installed'),
                self::FAIL => __('There was an error installing Zikula Blocks Module')
            ),
            11 => array(
                self::NAME => "security",
                self::PRE => __('Zikula Security Module'),
                self::DURING => __('Installing Zikula Security Module'),
                self::SUCCESS => __('Zikula Security Module installed'),
                self::FAIL => __('There was an error installing Zikula Security Module')
            ),
            12 => array(
                self::NAME => "categories",
                self::PRE => __('Zikula Categories Module'),
                self::DURING => __('Installing Zikula Categories Module'),
                self::SUCCESS => __('Zikula Categories Module installed'),
                self::FAIL => __('There was an error installing Zikula Categories Module')
            ),
            13 => array(
                self::NAME => "mailer",
                self::PRE => __('Zikula Mailer Module'),
                self::DURING => __('Installing Zikula Mailer Module'),
                self::SUCCESS => __('Zikula Mailer Module installed'),
                self::FAIL => __('There was an error installing Zikula Mailer Module')
            ),
            14 => array(
                self::NAME => "search",
                self::PRE => __('Zikula Search Module'),
                self::DURING => __('Installing Zikula Search Module'),
                self::SUCCESS => __('Zikula Search Module installed'),
                self::FAIL => __('There was an error installing Zikula Search Module')
            ),
            15 => array(
                self::NAME => "routes",
                self::PRE => __('Zikula Routes Module'),
                self::DURING => __('Installing Zikula Routes Module'),
                self::SUCCESS => __('Zikula Routes Module installed'),
                self::FAIL => __('There was an error installing Zikula Routes Module')
            ),
            16 => array(
                self::NAME => "activatemodules",
                self::PRE => __('Activate system modules'),
                self::DURING => __('Activating system modules'),
                self::SUCCESS => __('System modules activated'),
                self::FAIL => __('There was an error activating system modules')
            ),
            17 => array(
                self::NAME => "categorize",
                self::PRE => __('Module categorization'),
                self::DURING => __('Moving modules to their default categories'),
                self::SUCCESS => __('Modules moved to their default categories'),
                self::FAIL => __('There was an error moving modules to their default categories')
            ),
            18 => array(
                self::NAME => "createblocks",
                self::PRE => __('Create blocks'),
                self::DURING => __('Creating default blocks'),
                self::SUCCESS => __('Default blocks created'),
                self::FAIL => __('There was an error creating default blocks')
            ),
            19 => array(
                self::NAME => "updateadmin",
                self::PRE => __('Create admin account'),
                self::DURING => __('Creating admin account'),
                self::SUCCESS => __('Admin account created'),
                self::FAIL => __('There was an error creating admin account')
            ),
            20 => array(
                self::NAME => "loginadmin",
                self::PRE => __('Login'),
                self::DURING => __('Logging in as admin'),
                self::SUCCESS => __('Logged in as admin'),
                self::FAIL => __('There was an error logging in as admin')
            ),
            21 => array(
                self::NAME => "finalizeparameters",
                self::PRE => __('Finalize parameters'),
                self::DURING => __('Finalizing parameters'),
                self::SUCCESS => __('Parameters finalized'),
                self::FAIL => __('There was an error finalizing the parameters')
            ),
            22 => array(
                self::NAME => "reloadroutes",
                self::PRE => __('Reload routes'),
                self::DURING => __('Reloading routes (takes longer...)'),
                self::SUCCESS => __('Routes reloaded'),
                self::FAIL => __('There was an error reloading the routes')
            ),
            23 => array(
                self::NAME => "plugins",
                self::PRE => __('System Plugins'),
                self::DURING => __('Installing System Plugins'),
                self::SUCCESS => __('System Plugins installed'),
                self::FAIL => __('There was an error installing System Plugins')
            ),
            24 => array(
                self::NAME => "protect",
                self::PRE => __('Protect configuration files'),
                self::DURING => __('Protecting configuration files'),
                self::SUCCESS => __('Configuration files protected'),
                self::FAIL => __('There was an error protecting configuration files')
            ),
            25 => [
                self::NAME => "installassets",
                self::PRE => __('Install assets'),
                self::DURING => __('Installing assets to /web'),
                self::SUCCESS => __('Assets installed'),
                self::FAIL => __('Failed to install assets')
            ],
            26 => array(
                self::NAME => "finish",
                self::PRE => __('Finish'),
                self::DURING => __('Finish'),
                self::SUCCESS => __('Finish'),
                self::FAIL => __('Finish')
            )
        ));
    }
}
