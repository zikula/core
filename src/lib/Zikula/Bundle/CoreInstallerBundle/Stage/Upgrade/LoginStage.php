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

use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;

class LoginStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    private $container;
    private $yamlManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function getName()
    {
        return 'login';
    }

    public function getFormType()
    {
        return 'Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType';
    }

    public function getFormOptions()
    {
        return [];
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle::login.html.twig';
    }

    public function handleFormResult(FormInterface $form)
    {
        $this->container->get('core_installer.controller.util')->writeEncodedAdminCredentials($this->yamlManager, $form->getData());
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
