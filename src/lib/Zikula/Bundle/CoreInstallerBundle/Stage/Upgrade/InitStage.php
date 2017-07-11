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

use Symfony\Component\DependencyInjection\ContainerInterface;
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

    public function getName()
    {
        return 'init';
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Migration:migrate.html.twig';
    }

    public function isNecessary()
    {
        $migrationHelper = $this->container->get('zikula_core_installer.helper.migration_helper');
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

    public function getTemplateParams()
    {
        return ['count' => $this->count];
    }
}
