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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
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
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getProjectDir() . '/config', 'services_custom.yaml');
    }

    public function getName(): string
    {
        return 'createadmin';
    }

    public function getFormType(): string
    {
        return CreateAdminType::class;
    }

    public function getFormOptions(): array
    {
        return [];
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/createadmin.html.twig';
    }

    public function isNecessary(): bool
    {
        $params = $this->yamlManager->getParameters();

        return !(!empty($params['username']) && !empty($params['password']) && !empty($params['email']));
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function handleFormResult(FormInterface $form): bool
    {
        $this->container->get(ControllerHelper::class)->writeEncodedAdminCredentials($this->yamlManager, $form->getData());

        return true;
    }
}
