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

use Zikula\Bundle\CoreInstallerBundle\Stage\AjaxStageInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class AjaxInstallerStage implements AjaxStageInterface
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
                AjaxStageInterface::NAME => 'bundles',
                AjaxStageInterface::PRE => $this->__('Symfony Bundles'),
                AjaxStageInterface::DURING => $this->__('Persisting Symfony Bundles'),
                AjaxStageInterface::SUCCESS => $this->__('Symfony Bundles persisted'),
                AjaxStageInterface::FAIL => $this->__('There was an error persisting the Symfony Bundles')
            ],
            2 => [
                AjaxStageInterface::NAME => 'install_event',
                AjaxStageInterface::PRE => $this->__('Fire install event'),
                AjaxStageInterface::DURING => $this->__('Firing install event'),
                AjaxStageInterface::SUCCESS => $this->__('Fired install event'),
                AjaxStageInterface::FAIL => $this->__('There was an error firing the install event')
            ],
            3 => [
                AjaxStageInterface::NAME => 'extensions',
                AjaxStageInterface::PRE => $this->__('Zikula Extension Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Extensions Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Extensions Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Extensions Module')
            ],
            4 => [
                AjaxStageInterface::NAME => 'settings',
                AjaxStageInterface::PRE => $this->__('Zikula Settings Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Settings Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Settings Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Settings Module')
            ],
            5 => [
                AjaxStageInterface::NAME => 'theme',
                AjaxStageInterface::PRE => $this->__('Zikula Theme Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Theme Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Theme Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Theme Module')
            ],
            6 => [
                AjaxStageInterface::NAME => 'admin',
                AjaxStageInterface::PRE => $this->__('Zikula Administration Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Administration Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Administration Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Administration Module')
            ],
            7 => [
                AjaxStageInterface::NAME => 'permissions',
                AjaxStageInterface::PRE => $this->__('Zikula Permissions Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Permissions Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Permissions Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Permissions Module')
            ],
            8 => [
                AjaxStageInterface::NAME => 'users',
                AjaxStageInterface::PRE => $this->__('Zikula Users Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Users Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Users Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Users Module')
            ],
            9 => [
                AjaxStageInterface::NAME => 'zauth',
                AjaxStageInterface::PRE => $this->__('Zikula ZAuth Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula ZAuth Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula ZAuth Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula ZAuth Module')
            ],
            10 => [
                AjaxStageInterface::NAME => 'groups',
                AjaxStageInterface::PRE => $this->__('Zikula Groups Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Groups Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Groups Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Groups Module')
            ],
            11 => [
                AjaxStageInterface::NAME => 'blocks',
                AjaxStageInterface::PRE => $this->__('Zikula Blocks Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Blocks Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Blocks Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Blocks Module')
            ],
            12 => [
                AjaxStageInterface::NAME => 'security',
                AjaxStageInterface::PRE => $this->__('Zikula Security Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Security Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Security Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Security Module')
            ],
            13 => [
                AjaxStageInterface::NAME => 'categories',
                AjaxStageInterface::PRE => $this->__('Zikula Categories Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Categories Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Categories Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Categories Module')
            ],
            14 => [
                AjaxStageInterface::NAME => 'mailer',
                AjaxStageInterface::PRE => $this->__('Zikula Mailer Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Mailer Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Mailer Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Mailer Module')
            ],
            15 => [
                AjaxStageInterface::NAME => 'search',
                AjaxStageInterface::PRE => $this->__('Zikula Search Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Search Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Search Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Search Module')
            ],
            16 => [
                AjaxStageInterface::NAME => 'routes',
                AjaxStageInterface::PRE => $this->__('Zikula Routes Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Routes Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Routes Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Routes Module')
            ],
            17 => [
                AjaxStageInterface::NAME => 'menu',
                AjaxStageInterface::PRE => $this->__('Zikula Menu Module'),
                AjaxStageInterface::DURING => $this->__('Installing Zikula Menu Module'),
                AjaxStageInterface::SUCCESS => $this->__('Zikula Menu Module installed'),
                AjaxStageInterface::FAIL => $this->__('There was an error installing Zikula Menu Module')
            ],
            18 => [
                AjaxStageInterface::NAME => 'activatemodules',
                AjaxStageInterface::PRE => $this->__('Activate system modules'),
                AjaxStageInterface::DURING => $this->__('Activating system modules'),
                AjaxStageInterface::SUCCESS => $this->__('System modules activated'),
                AjaxStageInterface::FAIL => $this->__('There was an error activating system modules')
            ],
            19 => [
                AjaxStageInterface::NAME => 'categorize',
                AjaxStageInterface::PRE => $this->__('Module categorization'),
                AjaxStageInterface::DURING => $this->__('Moving modules to their default categories'),
                AjaxStageInterface::SUCCESS => $this->__('Modules moved to their default categories'),
                AjaxStageInterface::FAIL => $this->__('There was an error moving modules to their default categories')
            ],
            20 => [
                AjaxStageInterface::NAME => 'createblocks',
                AjaxStageInterface::PRE => $this->__('Create blocks'),
                AjaxStageInterface::DURING => $this->__('Creating default blocks'),
                AjaxStageInterface::SUCCESS => $this->__('Default blocks created'),
                AjaxStageInterface::FAIL => $this->__('There was an error creating default blocks')
            ],
            21 => [
                AjaxStageInterface::NAME => 'updateadmin',
                AjaxStageInterface::PRE => $this->__('Create admin account'),
                AjaxStageInterface::DURING => $this->__('Creating admin account'),
                AjaxStageInterface::SUCCESS => $this->__('Admin account created'),
                AjaxStageInterface::FAIL => $this->__('There was an error creating admin account')
            ],
            22 => [
                AjaxStageInterface::NAME => 'loginadmin',
                AjaxStageInterface::PRE => $this->__('Login'),
                AjaxStageInterface::DURING => $this->__('Logging in as admin'),
                AjaxStageInterface::SUCCESS => $this->__('Logged in as admin'),
                AjaxStageInterface::FAIL => $this->__('There was an error logging in as admin')
            ],
            23 => [
                AjaxStageInterface::NAME => 'finalizeparameters',
                AjaxStageInterface::PRE => $this->__('Finalize parameters'),
                AjaxStageInterface::DURING => $this->__('Finalizing parameters'),
                AjaxStageInterface::SUCCESS => $this->__('Parameters finalized'),
                AjaxStageInterface::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            24 => [
                AjaxStageInterface::NAME => 'protect',
                AjaxStageInterface::PRE => $this->__('Protect configuration files'),
                AjaxStageInterface::DURING => $this->__('Protecting configuration files'),
                AjaxStageInterface::SUCCESS => $this->__('Configuration files protected'),
                AjaxStageInterface::FAIL => $this->__('There was an error protecting configuration files')
            ],
            25 => [
                AjaxStageInterface::NAME => 'installassets',
                AjaxStageInterface::PRE => $this->__('Install assets'),
                AjaxStageInterface::DURING => $this->__('Installing assets to /web'),
                AjaxStageInterface::SUCCESS => $this->__('Assets installed'),
                AjaxStageInterface::FAIL => $this->__('Failed to install assets')
            ],
            26 => [
                AjaxStageInterface::NAME => 'finish',
                AjaxStageInterface::PRE => $this->__('Finish'),
                AjaxStageInterface::DURING => $this->__('Finish'),
                AjaxStageInterface::SUCCESS => $this->__('Finish'),
                AjaxStageInterface::FAIL => $this->__('Finish')
            ],
        ]];
    }
}
