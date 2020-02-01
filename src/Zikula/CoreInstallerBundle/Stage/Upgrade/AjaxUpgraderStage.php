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

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Bundle\CoreInstallerBundle\Stage\AjaxStageInterface;

class AjaxUpgraderStage implements AjaxStageInterface
{
    use TranslatorTrait;

    /**
     * @var string
     */
    private $oldVersion;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params
    ) {
        $this->setTranslator($translator);
        $this->oldVersion = $params->has(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM) ? $params->get(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM) : '';
    }

    public function getName(): string
    {
        return 'ajaxupgrader';
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Upgrade/ajaxupgrader.html.twig';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return ['stages' => [
            1 => [
                AjaxStageInterface::NAME => 'reinitparams',
                AjaxStageInterface::PRE => $this->trans('Reinitialize parameters'),
                AjaxStageInterface::DURING => $this->trans('Reinitializing parameters'),
                AjaxStageInterface::SUCCESS => $this->trans('Reinitialized parameters'),
                AjaxStageInterface::FAIL => $this->trans('There was an error reinitialize parameters')
            ],
            2 => [
                AjaxStageInterface::NAME => 'loginadmin',
                AjaxStageInterface::PRE => $this->trans('Login'),
                AjaxStageInterface::DURING => $this->trans('Logging in as admin'),
                AjaxStageInterface::SUCCESS => $this->trans('Logged in as admin'),
                AjaxStageInterface::FAIL => $this->trans('There was an error logging in as admin')
            ],
            3 => [
                AjaxStageInterface::NAME => 'upgrade_event',
                AjaxStageInterface::PRE => $this->trans('Fire upgrade event'),
                AjaxStageInterface::DURING => $this->trans('Firing upgrade event'),
                AjaxStageInterface::SUCCESS => $this->trans('Fired upgrade event'),
                AjaxStageInterface::FAIL => $this->trans('There was an error firing the upgrade event')
            ],
            4 => [
                AjaxStageInterface::NAME => 'upgradeextensions',
                AjaxStageInterface::PRE => $this->trans('Upgrade extensions'),
                AjaxStageInterface::DURING => $this->trans('Upgrading extensions'),
                AjaxStageInterface::SUCCESS => $this->trans('Extensions upgraded'),
                AjaxStageInterface::FAIL => $this->trans('There was an error upgrading the extensions')
            ],
            5 => [
                AjaxStageInterface::NAME => 'versionupgrade',
                AjaxStageInterface::PRE => $this->trans('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => $this->oldVersion, '%newVersion%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::DURING => $this->trans('Upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::SUCCESS => $this->trans('Upgraded to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::FAIL => $this->trans('There was an error upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION])
            ],
            6 => [
                AjaxStageInterface::NAME => 'finalizeparameters',
                AjaxStageInterface::PRE => $this->trans('Finalize parameters'),
                AjaxStageInterface::DURING => $this->trans('Finalizing parameters'),
                AjaxStageInterface::SUCCESS => $this->trans('Parameters finalized'),
                AjaxStageInterface::FAIL => $this->trans('There was an error finalizing the parameters')
            ],
            7 => [
                AjaxStageInterface::NAME => 'clearcaches',
                AjaxStageInterface::PRE => $this->trans('Clear caches'),
                AjaxStageInterface::DURING => $this->trans('Clearing caches'),
                AjaxStageInterface::SUCCESS => $this->trans('Caches cleared'),
                AjaxStageInterface::FAIL => $this->trans('There was an error clearing caches')
            ],
            8 => [
                AjaxStageInterface::NAME => 'finish',
                AjaxStageInterface::PRE => $this->trans('Finish'),
                AjaxStageInterface::DURING => $this->trans('Finish'),
                AjaxStageInterface::SUCCESS => $this->trans('Finish'),
                AjaxStageInterface::FAIL => $this->trans('Finish')
            ]
        ]];
    }
}
