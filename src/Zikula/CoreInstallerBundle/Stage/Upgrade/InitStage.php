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
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class InitStage implements StageInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $count;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'init';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Migration/migrate.html.twig';
    }

    public function isNecessary(): bool
    {
        $currentVersion = $this->container->getParameter('installed');
        if (version_compare($currentVersion, '2.0.0', '>=')) {
            return false;
        }

        $migrationHelper = $this->container->get(MigrationHelper::class);
        $this->count = $migrationHelper->countUnMigratedUsers();
        if ($this->count > 0) {
            $this->container->get('session')->set('user_migration_count', $this->count);
            $this->container->get('session')->set('user_migration_complete', 0);
            $this->container->get('session')->set('user_migration_lastuid', 0);
            $this->container->get('session')->set('user_migration_maxuid', $migrationHelper->getMaxUnMigratedUid());

            return true;
        }

        return false;
    }

    public function getTemplateParams(): array
    {
        return [
            'count' => $this->count
        ];
    }
}
