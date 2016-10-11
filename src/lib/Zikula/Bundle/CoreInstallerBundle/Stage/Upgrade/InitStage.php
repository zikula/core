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
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class InitStage implements StageInterface, InjectContainerInterface
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
        return 'init';
    }

    public function getTemplateName()
    {
        return "";
    }

    public function isNecessary()
    {
        $currentVersion = $this->container->getParameter(\ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare(\ZikulaKernel::VERSION, '2.0.0', '>') && version_compare($currentVersion, '2.0.0', '>=')) {
            // this stage is not necessary to upgrade from 2.0.0 -> 2.0.x
            return false;
        }
        $this->init();

        return false;
    }

    public function getTemplateParams()
    {
        return [];
    }

    private function init()
    {
    }
}
