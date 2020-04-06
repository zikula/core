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

use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\StageInterface;

class LoginStage implements StageInterface, FormHandlerInterface
{
    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var YamlDumper
     */
    private $yamlManager;

    public function __construct(ControllerHelper $controllerHelper, string $projectDir)
    {
        $this->controllerHelper = $controllerHelper;
        $this->yamlManager = new YamlDumper($projectDir . '/config', 'services_custom.yaml');
    }

    public function getName(): string
    {
        return 'login';
    }

    public function getFormType(): string
    {
        return LoginType::class;
    }

    public function getFormOptions(): array
    {
        return [];
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/login.html.twig';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function handleFormResult(FormInterface $form): bool
    {
        try {
            $this->controllerHelper->writeEncodedAdminCredentials($this->yamlManager, $form->getData());
        } catch (AbortStageException $exception) {
            return false;
        }

        return true;
    }
}
