<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class AjaxInstallerStage implements StageInterface, InjectContainerInterface
{
    use TranslatorTrait;

    const NAME = 'name';
    const PRE = 'pre';
    const DURING = 'during';
    const SUCCESS = 'success';
    const FAIL = 'fail';

    public function __construct(ContainerInterface $container)
    {
        $this->setTranslator($container->get('translator.default'));
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'ajaxinstaller';
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Install:ajaxinstaller.html.twig';
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return ['stages' => [
            1 => [
                self::NAME => 'bundles',
                self::PRE => $this->__('Symfony Bundles'),
                self::DURING => $this->__('Persisting Symfony Bundles'),
                self::SUCCESS => $this->__('Symfony Bundles persisted'),
                self::FAIL => $this->__('There was an error persisting the Symfony Bundles')
            ],
            2 => [
                self::NAME => 'install_event',
                self::PRE => $this->__('Fire install event'),
                self::DURING => $this->__('Firing install event'),
                self::SUCCESS => $this->__('Fired install event'),
                self::FAIL => $this->__('There was an error firing the install event')
            ],
            3 => [
                self::NAME => 'extensions',
                self::PRE => $this->__('Zikula Extension Module'),
                self::DURING => $this->__('Installing Zikula Extensions Module'),
                self::SUCCESS => $this->__('Zikula Extensions Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Extensions Module')
            ],
            4 => [
                self::NAME => 'settings',
                self::PRE => $this->__('Zikula Settings Module'),
                self::DURING => $this->__('Installing Zikula Settings Module'),
                self::SUCCESS => $this->__('Zikula Settings Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Settings Module')
            ],
            5 => [
                self::NAME => 'theme',
                self::PRE => $this->__('Zikula Theme Module'),
                self::DURING => $this->__('Installing Zikula Theme Module'),
                self::SUCCESS => $this->__('Zikula Theme Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Theme Module')
            ],
            6 => [
                self::NAME => 'admin',
                self::PRE => $this->__('Zikula Administration Module'),
                self::DURING => $this->__('Installing Zikula Administration Module'),
                self::SUCCESS => $this->__('Zikula Administration Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Administration Module')
            ],
            7 => [
                self::NAME => 'permissions',
                self::PRE => $this->__('Zikula Permissions Module'),
                self::DURING => $this->__('Installing Zikula Permissions Module'),
                self::SUCCESS => $this->__('Zikula Permissions Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Permissions Module')
            ],
            8 => [
                self::NAME => 'users',
                self::PRE => $this->__('Zikula Users Module'),
                self::DURING => $this->__('Installing Zikula Users Module'),
                self::SUCCESS => $this->__('Zikula Users Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Users Module')
            ],
            9 => [
                self::NAME => 'zauth',
                self::PRE => $this->__('Zikula ZAuth Module'),
                self::DURING => $this->__('Installing Zikula ZAuth Module'),
                self::SUCCESS => $this->__('Zikula ZAuth Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula ZAuth Module')
            ],
            10 => [
                self::NAME => 'groups',
                self::PRE => $this->__('Zikula Groups Module'),
                self::DURING => $this->__('Installing Zikula Groups Module'),
                self::SUCCESS => $this->__('Zikula Groups Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Groups Module')
            ],
            11 => [
                self::NAME => 'blocks',
                self::PRE => $this->__('Zikula Blocks Module'),
                self::DURING => $this->__('Installing Zikula Blocks Module'),
                self::SUCCESS => $this->__('Zikula Blocks Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Blocks Module')
            ],
            12 => [
                self::NAME => 'security',
                self::PRE => $this->__('Zikula Security Module'),
                self::DURING => $this->__('Installing Zikula Security Module'),
                self::SUCCESS => $this->__('Zikula Security Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Security Module')
            ],
            13 => [
                self::NAME => 'categories',
                self::PRE => $this->__('Zikula Categories Module'),
                self::DURING => $this->__('Installing Zikula Categories Module'),
                self::SUCCESS => $this->__('Zikula Categories Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Categories Module')
            ],
            14 => [
                self::NAME => 'mailer',
                self::PRE => $this->__('Zikula Mailer Module'),
                self::DURING => $this->__('Installing Zikula Mailer Module'),
                self::SUCCESS => $this->__('Zikula Mailer Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Mailer Module')
            ],
            15 => [
                self::NAME => 'search',
                self::PRE => $this->__('Zikula Search Module'),
                self::DURING => $this->__('Installing Zikula Search Module'),
                self::SUCCESS => $this->__('Zikula Search Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Search Module')
            ],
            16 => [
                self::NAME => 'routes',
                self::PRE => $this->__('Zikula Routes Module'),
                self::DURING => $this->__('Installing Zikula Routes Module'),
                self::SUCCESS => $this->__('Zikula Routes Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Routes Module')
            ],
            17 => [
                self::NAME => 'menu',
                self::PRE => $this->__('Zikula Menu Module'),
                self::DURING => $this->__('Installing Zikula Menu Module'),
                self::SUCCESS => $this->__('Zikula Menu Module installed'),
                self::FAIL => $this->__('There was an error installing Zikula Menu Module')
            ],
            18 => [
                self::NAME => 'activatemodules',
                self::PRE => $this->__('Activate system modules'),
                self::DURING => $this->__('Activating system modules'),
                self::SUCCESS => $this->__('System modules activated'),
                self::FAIL => $this->__('There was an error activating system modules')
            ],
            19 => [
                self::NAME => 'categorize',
                self::PRE => $this->__('Module categorization'),
                self::DURING => $this->__('Moving modules to their default categories'),
                self::SUCCESS => $this->__('Modules moved to their default categories'),
                self::FAIL => $this->__('There was an error moving modules to their default categories')
            ],
            20 => [
                self::NAME => 'createblocks',
                self::PRE => $this->__('Create blocks'),
                self::DURING => $this->__('Creating default blocks'),
                self::SUCCESS => $this->__('Default blocks created'),
                self::FAIL => $this->__('There was an error creating default blocks')
            ],
            21 => [
                self::NAME => 'updateadmin',
                self::PRE => $this->__('Create admin account'),
                self::DURING => $this->__('Creating admin account'),
                self::SUCCESS => $this->__('Admin account created'),
                self::FAIL => $this->__('There was an error creating admin account')
            ],
            22 => [
                self::NAME => 'loginadmin',
                self::PRE => $this->__('Login'),
                self::DURING => $this->__('Logging in as admin'),
                self::SUCCESS => $this->__('Logged in as admin'),
                self::FAIL => $this->__('There was an error logging in as admin')
            ],
            23 => [
                self::NAME => 'finalizeparameters',
                self::PRE => $this->__('Finalize parameters'),
                self::DURING => $this->__('Finalizing parameters'),
                self::SUCCESS => $this->__('Parameters finalized'),
                self::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            24 => [
                self::NAME => 'reloadroutes',
                self::PRE => $this->__('Reload routes'),
                self::DURING => $this->__('Reloading routes (takes longer...)'),
                self::SUCCESS => $this->__('Routes reloaded'),
                self::FAIL => $this->__('There was an error reloading the routes')
            ],
            25 => [
                self::NAME => 'plugins',
                self::PRE => $this->__('System Plugins'),
                self::DURING => $this->__('Installing System Plugins'),
                self::SUCCESS => $this->__('System Plugins installed'),
                self::FAIL => $this->__('There was an error installing System Plugins')
            ],
            26 => [
                self::NAME => 'protect',
                self::PRE => $this->__('Protect configuration files'),
                self::DURING => $this->__('Protecting configuration files'),
                self::SUCCESS => $this->__('Configuration files protected'),
                self::FAIL => $this->__('There was an error protecting configuration files')
            ],
            27 => [
                self::NAME => 'installassets',
                self::PRE => $this->__('Install assets'),
                self::DURING => $this->__('Installing assets to /web'),
                self::SUCCESS => $this->__('Assets installed'),
                self::FAIL => $this->__('Failed to install assets')
            ],
            28 => [
                self::NAME => 'finish',
                self::PRE => $this->__('Finish'),
                self::DURING => $this->__('Finish'),
                self::SUCCESS => $this->__('Finish'),
                self::FAIL => $this->__('Finish')
            ]
        ]];
    }
}
