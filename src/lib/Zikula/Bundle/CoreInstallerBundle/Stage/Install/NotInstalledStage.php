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

class NotInstalledStage implements StageInterface
{
    public function getName()
    {
        return 'notinstalled';
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:notinstalled.html.twig";
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }
}
