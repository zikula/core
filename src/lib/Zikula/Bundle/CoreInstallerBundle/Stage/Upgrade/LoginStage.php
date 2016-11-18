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
        return [
            'translator' => $this->container->get('translator.default')
        ];
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle::login.html.twig';
    }

    public function handleFormResult(FormInterface $form)
    {
        $this->container->get('zikula_core_installer.controller.util')->writeEncodedAdminCredentials($this->yamlManager, $form->getData());
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
