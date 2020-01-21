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
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Bundle\CoreInstallerBundle\Stage\AjaxStageInterface;
use Zikula\Component\Wizard\InjectContainerInterface;

class AjaxInstallerStage implements AjaxStageInterface, InjectContainerInterface
{
    use TranslatorTrait;

    public function __construct(ContainerInterface $container = null)
    {
        if (isset($container)) {
            $this->setTranslator($container->get('translator'));
        }
    }

    public function getName(): string
    {
        return 'ajaxinstaller';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/ajaxinstaller.html.twig';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return ['stages' => [
            1 => [
                AjaxStageInterface::NAME => 'bundles',
                AjaxStageInterface::PRE => $this->trans('Symfony Bundles'),
                AjaxStageInterface::DURING => $this->trans('Persisting Symfony Bundles'),
                AjaxStageInterface::SUCCESS => $this->trans('Symfony Bundles persisted'),
                AjaxStageInterface::FAIL => $this->trans('There was an error persisting the Symfony Bundles')
            ],
            2 => [
                AjaxStageInterface::NAME => 'install_event',
                AjaxStageInterface::PRE => $this->trans('Fire install event'),
                AjaxStageInterface::DURING => $this->trans('Firing install event'),
                AjaxStageInterface::SUCCESS => $this->trans('Fired install event'),
                AjaxStageInterface::FAIL => $this->trans('There was an error firing the install event')
            ],
            3 => [
                AjaxStageInterface::NAME => 'extensions',
                AjaxStageInterface::PRE => $this->trans('Zikula Extension Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Extensions Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Extensions Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Extensions Module')
            ],
            4 => [
                AjaxStageInterface::NAME => 'settings',
                AjaxStageInterface::PRE => $this->trans('Zikula Settings Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Settings Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Settings Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Settings Module')
            ],
            5 => [
                AjaxStageInterface::NAME => 'theme',
                AjaxStageInterface::PRE => $this->trans('Zikula Theme Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Theme Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Theme Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Theme Module')
            ],
            6 => [
                AjaxStageInterface::NAME => 'admin',
                AjaxStageInterface::PRE => $this->trans('Zikula Administration Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Administration Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Administration Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Administration Module')
            ],
            7 => [
                AjaxStageInterface::NAME => 'permissions',
                AjaxStageInterface::PRE => $this->trans('Zikula Permissions Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Permissions Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Permissions Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Permissions Module')
            ],
            8 => [
                AjaxStageInterface::NAME => 'users',
                AjaxStageInterface::PRE => $this->trans('Zikula Users Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Users Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Users Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Users Module')
            ],
            9 => [
                AjaxStageInterface::NAME => 'zauth',
                AjaxStageInterface::PRE => $this->trans('Zikula ZAuth Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula ZAuth Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula ZAuth Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula ZAuth Module')
            ],
            10 => [
                AjaxStageInterface::NAME => 'groups',
                AjaxStageInterface::PRE => $this->trans('Zikula Groups Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Groups Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Groups Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Groups Module')
            ],
            11 => [
                AjaxStageInterface::NAME => 'blocks',
                AjaxStageInterface::PRE => $this->trans('Zikula Blocks Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Blocks Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Blocks Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Blocks Module')
            ],
            12 => [
                AjaxStageInterface::NAME => 'security',
                AjaxStageInterface::PRE => $this->trans('Zikula Security Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Security Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Security Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Security Module')
            ],
            13 => [
                AjaxStageInterface::NAME => 'categories',
                AjaxStageInterface::PRE => $this->trans('Zikula Categories Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Categories Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Categories Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Categories Module')
            ],
            14 => [
                AjaxStageInterface::NAME => 'mailer',
                AjaxStageInterface::PRE => $this->trans('Zikula Mailer Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Mailer Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Mailer Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Mailer Module')
            ],
            15 => [
                AjaxStageInterface::NAME => 'search',
                AjaxStageInterface::PRE => $this->trans('Zikula Search Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Search Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Search Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Search Module')
            ],
            16 => [
                AjaxStageInterface::NAME => 'routes',
                AjaxStageInterface::PRE => $this->trans('Zikula Routes Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Routes Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Routes Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Routes Module')
            ],
            17 => [
                AjaxStageInterface::NAME => 'menu',
                AjaxStageInterface::PRE => $this->trans('Zikula Menu Module'),
                AjaxStageInterface::DURING => $this->trans('Installing Zikula Menu Module'),
                AjaxStageInterface::SUCCESS => $this->trans('Zikula Menu Module installed'),
                AjaxStageInterface::FAIL => $this->trans('There was an error installing Zikula Menu Module')
            ],
            18 => [
                AjaxStageInterface::NAME => 'activatemodules',
                AjaxStageInterface::PRE => $this->trans('Activate system modules'),
                AjaxStageInterface::DURING => $this->trans('Activating system modules'),
                AjaxStageInterface::SUCCESS => $this->trans('System modules activated'),
                AjaxStageInterface::FAIL => $this->trans('There was an error activating system modules')
            ],
            19 => [
                AjaxStageInterface::NAME => 'categorize',
                AjaxStageInterface::PRE => $this->trans('Module categorization'),
                AjaxStageInterface::DURING => $this->trans('Moving modules to their default categories'),
                AjaxStageInterface::SUCCESS => $this->trans('Modules moved to their default categories'),
                AjaxStageInterface::FAIL => $this->trans('There was an error moving modules to their default categories')
            ],
            20 => [
                AjaxStageInterface::NAME => 'createblocks',
                AjaxStageInterface::PRE => $this->trans('Create blocks'),
                AjaxStageInterface::DURING => $this->trans('Creating default blocks'),
                AjaxStageInterface::SUCCESS => $this->trans('Default blocks created'),
                AjaxStageInterface::FAIL => $this->trans('There was an error creating default blocks')
            ],
            21 => [
                AjaxStageInterface::NAME => 'updateadmin',
                AjaxStageInterface::PRE => $this->trans('Create admin account'),
                AjaxStageInterface::DURING => $this->trans('Creating admin account'),
                AjaxStageInterface::SUCCESS => $this->trans('Admin account created'),
                AjaxStageInterface::FAIL => $this->trans('There was an error creating admin account')
            ],
            22 => [
                AjaxStageInterface::NAME => 'loginadmin',
                AjaxStageInterface::PRE => $this->trans('Login'),
                AjaxStageInterface::DURING => $this->trans('Logging in as admin'),
                AjaxStageInterface::SUCCESS => $this->trans('Logged in as admin'),
                AjaxStageInterface::FAIL => $this->trans('There was an error logging in as admin')
            ],
            23 => [
                AjaxStageInterface::NAME => 'finalizeparameters',
                AjaxStageInterface::PRE => $this->trans('Finalize parameters'),
                AjaxStageInterface::DURING => $this->trans('Finalizing parameters'),
                AjaxStageInterface::SUCCESS => $this->trans('Parameters finalized'),
                AjaxStageInterface::FAIL => $this->trans('There was an error finalizing the parameters')
            ],
            24 => [
                AjaxStageInterface::NAME => 'protect',
                AjaxStageInterface::PRE => $this->trans('Protect configuration files'),
                AjaxStageInterface::DURING => $this->trans('Protecting configuration files'),
                AjaxStageInterface::SUCCESS => $this->trans('Configuration files protected'),
                AjaxStageInterface::FAIL => $this->trans('There was an error protecting configuration files')
            ],
            25 => [
                AjaxStageInterface::NAME => 'installassets',
                AjaxStageInterface::PRE => $this->trans('Install assets'),
                AjaxStageInterface::DURING => $this->trans('Installing assets to /web'),
                AjaxStageInterface::SUCCESS => $this->trans('Assets installed'),
                AjaxStageInterface::FAIL => $this->trans('Failed to install assets')
            ],
            26 => [
                AjaxStageInterface::NAME => 'finish',
                AjaxStageInterface::PRE => $this->trans('Finish'),
                AjaxStageInterface::DURING => $this->trans('Finish'),
                AjaxStageInterface::SUCCESS => $this->trans('Finish'),
                AjaxStageInterface::FAIL => $this->trans('Finish')
            ],
        ]];
    }
}
