<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Zikula\Component\Wizard\StageInterface;

class AlreadyInstalledStage implements StageInterface
{
    public function getName()
    {
        return 'alreadyinstalled';
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Install:alreadyinstalled.html.twig';
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return [];
    }
}
