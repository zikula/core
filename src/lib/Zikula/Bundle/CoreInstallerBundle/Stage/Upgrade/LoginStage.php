<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class LoginStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    private $container;

    private $yamlManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() . '/config', 'custom_parameters.yml');
    }

    public function getName()
    {
        return 'login';
    }

    public function getFormType()
    {
        return LoginType::class;
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
        $this->container->get(ControllerHelper::class)->writeEncodedAdminCredentials($this->yamlManager, $form->getData());
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
