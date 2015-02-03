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
        return "ZikulaCoreInstallerBundle:Install:requirements.html.twig";
    }

    public function isNecessary()
    {
        $this->requirementsMet = $this->container->get('core_installer.controller.util')->requirementsMet($this->container);
        if ($this->requirementsMet === true) {

            return false;
        }

        return true;
    }

    public function getTemplateParams()
    {
        return array('checks' => $this->requirementsMet);
    }
}