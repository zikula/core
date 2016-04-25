<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        return 'ZikulaCoreInstallerBundle:Install:notinstalled.html.twig';
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
