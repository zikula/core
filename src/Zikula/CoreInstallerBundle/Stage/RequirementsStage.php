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

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
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

    public function getName(): string
    {
        return 'requirements';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/requirements.html.twig';
    }

    public function isNecessary(): bool
    {
        $this->requirementsMet = $this->container->get(ControllerHelper::class)->requirementsMet();

        return !$this->requirementsMet;
    }

    public function getTemplateParams(): array
    {
        return [
            'checks' => $this->requirementsMet
        ];
    }
}
