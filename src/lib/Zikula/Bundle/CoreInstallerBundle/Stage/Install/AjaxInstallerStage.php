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

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Component\Wizard\StageInterface;

class AjaxInstallerStage implements StageInterface
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
                StageInterface::NAME => 'bundles',
                StageInterface::PRE => $this->__('Symfony Bundles'),
                StageInterface::DURING => $this->__('Persisting Symfony Bundles'),
                StageInterface::SUCCESS => $this->__('Symfony Bundles persisted'),
                StageInterface::FAIL => $this->__('There was an error persisting the Symfony Bundles')
            ],
            2 => [
                StageInterface::NAME => 'install_event',
                StageInterface::PRE => $this->__('Fire install event'),
                StageInterface::DURING => $this->__('Firing install event'),
                StageInterface::SUCCESS => $this->__('Fired install event'),
                StageInterface::FAIL => $this->__('There was an error firing the install event')
            ],
            3 => [
                StageInterface::NAME => 'extensions',
                StageInterface::PRE => $this->__('Zikula Extension Module'),
                StageInterface::DURING => $this->__('Installing Zikula Extensions Module'),
                StageInterface::SUCCESS => $this->__('Zikula Extensions Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Extensions Module')
            ],
            4 => [
                StageInterface::NAME => 'settings',
                StageInterface::PRE => $this->__('Zikula Settings Module'),
                StageInterface::DURING => $this->__('Installing Zikula Settings Module'),
                StageInterface::SUCCESS => $this->__('Zikula Settings Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Settings Module')
            ],
            5 => [
                StageInterface::NAME => 'theme',
                StageInterface::PRE => $this->__('Zikula Theme Module'),
                StageInterface::DURING => $this->__('Installing Zikula Theme Module'),
                StageInterface::SUCCESS => $this->__('Zikula Theme Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Theme Module')
            ],
            6 => [
                StageInterface::NAME => 'admin',
                StageInterface::PRE => $this->__('Zikula Administration Module'),
                StageInterface::DURING => $this->__('Installing Zikula Administration Module'),
                StageInterface::SUCCESS => $this->__('Zikula Administration Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Administration Module')
            ],
            7 => [
                StageInterface::NAME => 'permissions',
                StageInterface::PRE => $this->__('Zikula Permissions Module'),
                StageInterface::DURING => $this->__('Installing Zikula Permissions Module'),
                StageInterface::SUCCESS => $this->__('Zikula Permissions Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Permissions Module')
            ],
            8 => [
                StageInterface::NAME => 'users',
                StageInterface::PRE => $this->__('Zikula Users Module'),
                StageInterface::DURING => $this->__('Installing Zikula Users Module'),
                StageInterface::SUCCESS => $this->__('Zikula Users Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Users Module')
            ],
            9 => [
                StageInterface::NAME => 'zauth',
                StageInterface::PRE => $this->__('Zikula ZAuth Module'),
                StageInterface::DURING => $this->__('Installing Zikula ZAuth Module'),
                StageInterface::SUCCESS => $this->__('Zikula ZAuth Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula ZAuth Module')
            ],
            10 => [
                StageInterface::NAME => 'groups',
                StageInterface::PRE => $this->__('Zikula Groups Module'),
                StageInterface::DURING => $this->__('Installing Zikula Groups Module'),
                StageInterface::SUCCESS => $this->__('Zikula Groups Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Groups Module')
            ],
            11 => [
                StageInterface::NAME => 'blocks',
                StageInterface::PRE => $this->__('Zikula Blocks Module'),
                StageInterface::DURING => $this->__('Installing Zikula Blocks Module'),
                StageInterface::SUCCESS => $this->__('Zikula Blocks Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Blocks Module')
            ],
            12 => [
                StageInterface::NAME => 'security',
                StageInterface::PRE => $this->__('Zikula Security Module'),
                StageInterface::DURING => $this->__('Installing Zikula Security Module'),
                StageInterface::SUCCESS => $this->__('Zikula Security Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Security Module')
            ],
            13 => [
                StageInterface::NAME => 'categories',
                StageInterface::PRE => $this->__('Zikula Categories Module'),
                StageInterface::DURING => $this->__('Installing Zikula Categories Module'),
                StageInterface::SUCCESS => $this->__('Zikula Categories Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Categories Module')
            ],
            14 => [
                StageInterface::NAME => 'mailer',
                StageInterface::PRE => $this->__('Zikula Mailer Module'),
                StageInterface::DURING => $this->__('Installing Zikula Mailer Module'),
                StageInterface::SUCCESS => $this->__('Zikula Mailer Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Mailer Module')
            ],
            15 => [
                StageInterface::NAME => 'search',
                StageInterface::PRE => $this->__('Zikula Search Module'),
                StageInterface::DURING => $this->__('Installing Zikula Search Module'),
                StageInterface::SUCCESS => $this->__('Zikula Search Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Search Module')
            ],
            16 => [
                StageInterface::NAME => 'routes',
                StageInterface::PRE => $this->__('Zikula Routes Module'),
                StageInterface::DURING => $this->__('Installing Zikula Routes Module'),
                StageInterface::SUCCESS => $this->__('Zikula Routes Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Routes Module')
            ],
            17 => [
                StageInterface::NAME => 'menu',
                StageInterface::PRE => $this->__('Zikula Menu Module'),
                StageInterface::DURING => $this->__('Installing Zikula Menu Module'),
                StageInterface::SUCCESS => $this->__('Zikula Menu Module installed'),
                StageInterface::FAIL => $this->__('There was an error installing Zikula Menu Module')
            ],
            18 => [
                StageInterface::NAME => 'activatemodules',
                StageInterface::PRE => $this->__('Activate system modules'),
                StageInterface::DURING => $this->__('Activating system modules'),
                StageInterface::SUCCESS => $this->__('System modules activated'),
                StageInterface::FAIL => $this->__('There was an error activating system modules')
            ],
            19 => [
                StageInterface::NAME => 'categorize',
                StageInterface::PRE => $this->__('Module categorization'),
                StageInterface::DURING => $this->__('Moving modules to their default categories'),
                StageInterface::SUCCESS => $this->__('Modules moved to their default categories'),
                StageInterface::FAIL => $this->__('There was an error moving modules to their default categories')
            ],
            20 => [
                StageInterface::NAME => 'createblocks',
                StageInterface::PRE => $this->__('Create blocks'),
                StageInterface::DURING => $this->__('Creating default blocks'),
                StageInterface::SUCCESS => $this->__('Default blocks created'),
                StageInterface::FAIL => $this->__('There was an error creating default blocks')
            ],
            21 => [
                StageInterface::NAME => 'updateadmin',
                StageInterface::PRE => $this->__('Create admin account'),
                StageInterface::DURING => $this->__('Creating admin account'),
                StageInterface::SUCCESS => $this->__('Admin account created'),
                StageInterface::FAIL => $this->__('There was an error creating admin account')
            ],
            22 => [
                StageInterface::NAME => 'loginadmin',
                StageInterface::PRE => $this->__('Login'),
                StageInterface::DURING => $this->__('Logging in as admin'),
                StageInterface::SUCCESS => $this->__('Logged in as admin'),
                StageInterface::FAIL => $this->__('There was an error logging in as admin')
            ],
            23 => [
                StageInterface::NAME => 'finalizeparameters',
                StageInterface::PRE => $this->__('Finalize parameters'),
                StageInterface::DURING => $this->__('Finalizing parameters'),
                StageInterface::SUCCESS => $this->__('Parameters finalized'),
                StageInterface::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            24 => [
                StageInterface::NAME => 'protect',
                StageInterface::PRE => $this->__('Protect configuration files'),
                StageInterface::DURING => $this->__('Protecting configuration files'),
                StageInterface::SUCCESS => $this->__('Configuration files protected'),
                StageInterface::FAIL => $this->__('There was an error protecting configuration files')
            ],
            25 => [
                StageInterface::NAME => 'installassets',
                StageInterface::PRE => $this->__('Install assets'),
                StageInterface::DURING => $this->__('Installing assets to /web'),
                StageInterface::SUCCESS => $this->__('Assets installed'),
                StageInterface::FAIL => $this->__('Failed to install assets')
            ],
            26 => [
                StageInterface::NAME => 'finish',
                StageInterface::PRE => $this->__('Finish'),
                StageInterface::DURING => $this->__('Finish'),
                StageInterface::SUCCESS => $this->__('Finish'),
                StageInterface::FAIL => $this->__('Finish')
            ],
        ]];
    }
}
