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

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Zikula\Component\Wizard\StageInterface;

class AjaxInstallerStage implements StageInterface
{
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
            1 => "bundles",
            2 => "extensions",
            3 => "settings",
            4 => "theme",
            5 => "admin",
            6 => "permissions",
            7 => "groups",
            8 => "blocks",
            9 => "users",
            10 => "security",
            11 => "categories",
            12 => "mailer",
            13 => "search",
            14 => "routes",
            15 => "activatemodules",
            16 => "categorize",
            17 => "createblocks",
            18 => "updateadmin",
            19 => "loginadmin",
            20 => "finalizeparameters",
            21 => "reloadroutes",
            22 => "plugins",
            23 => "protect",
            24 => "finish"
        ));
    }
}