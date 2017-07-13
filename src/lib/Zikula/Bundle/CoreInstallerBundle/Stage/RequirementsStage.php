<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class RequirementsStage implements StageInterface, InjectContainerInterface
{
    private $requirementsMet;

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
        return 'requirements';
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Install:requirements.html.twig';
    }

    public function isNecessary()
    {
        $this->requirementsMet = $this->container->get('zikula_core_installer.controller.helper')->requirementsMet();

        return !$this->requirementsMet;
    }

    public function getTemplateParams()
    {
        return ['checks' => $this->requirementsMet];
    }
}
