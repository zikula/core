<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

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
    private $installed;

    public function __construct(
        TranslatorInterface $translator,
        string $installed
    ) {
        $this->setTranslator($translator);
        $this->installed = $installed;
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
                AjaxStageInterface::NAME => 'upgrade_event',
                AjaxStageInterface::PRE => $this->trans('Fire upgrade event'),
                AjaxStageInterface::DURING => $this->trans('Firing upgrade event'),
                AjaxStageInterface::SUCCESS => $this->trans('Fired upgrade event'),
                AjaxStageInterface::FAIL => $this->trans('There was an error firing the upgrade event')
            ],
            3 => [
                AjaxStageInterface::NAME => 'upgradeextensions',
                AjaxStageInterface::PRE => $this->trans('Upgrade extensions'),
                AjaxStageInterface::DURING => $this->trans('Upgrading extensions'),
                AjaxStageInterface::SUCCESS => $this->trans('Extensions upgraded'),
                AjaxStageInterface::FAIL => $this->trans('There was an error upgrading the extensions')
            ],
            4 => [
                AjaxStageInterface::NAME => 'versionupgrade',
                AjaxStageInterface::PRE => $this->trans('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => $this->installed, '%newVersion%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::DURING => $this->trans('Upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::SUCCESS => $this->trans('Upgraded to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::FAIL => $this->trans('There was an error upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION])
            ],
            5 => [
                AjaxStageInterface::NAME => 'loginadmin',
                AjaxStageInterface::PRE => $this->trans('Login'),
                AjaxStageInterface::DURING => $this->trans('Logging in as admin'),
                AjaxStageInterface::SUCCESS => $this->trans('Logged in as admin'),
                AjaxStageInterface::FAIL => $this->trans('There was an error logging in as admin')
            ],
            6 => [
                AjaxStageInterface::NAME => 'finalizeparameters',
                AjaxStageInterface::PRE => $this->trans('Finalize parameters'),
                AjaxStageInterface::DURING => $this->trans('Finalizing parameters'),
                AjaxStageInterface::SUCCESS => $this->trans('Parameters finalized'),
                AjaxStageInterface::FAIL => $this->trans('There was an error finalizing the parameters')
            ],
            7 => [
                AjaxStageInterface::NAME => 'finish',
                AjaxStageInterface::PRE => $this->trans('Finish'),
                AjaxStageInterface::DURING => $this->trans('Finish'),
                AjaxStageInterface::SUCCESS => $this->trans('Finish'),
                AjaxStageInterface::FAIL => $this->trans('Finish')
            ]
        ]];
    }
}
