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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class CreateAdminStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    private $yamlManager;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function getName()
    {
        return 'createadmin';
    }

    public function getFormType()
    {
        return 'Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType';
    }

    public function getFormOptions()
    {
        return [];
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:createadmin.html.twig";
    }

    public function isNecessary()
    {
        $params = $this->yamlManager->getParameters();
        if (!empty($params['username']) && !empty($params['password']) && !empty($params['email'])) {
            return false;
        }

        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }

    public function handleFormResult(FormInterface $form)
    {
        $this->container->get('core_installer.controller.util')->writeEncodedAdminCredentials($this->yamlManager, $form->getData());
    }
}
