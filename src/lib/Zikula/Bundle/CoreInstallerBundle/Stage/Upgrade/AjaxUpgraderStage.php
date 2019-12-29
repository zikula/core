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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Stage\AjaxStageInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

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
        $this->oldVersion = $params[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM] ?? '';
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
                AjaxStageInterface::PRE => $this->__('Reinitialize parameters'),
                AjaxStageInterface::DURING => $this->__('Reinitializing parameters'),
                AjaxStageInterface::SUCCESS => $this->__('Reinitialized parameters'),
                AjaxStageInterface::FAIL => $this->__('There was an error reinitialize parameters')
            ],
            2 => [
                AjaxStageInterface::NAME => 'loginadmin',
                AjaxStageInterface::PRE => $this->__('Login'),
                AjaxStageInterface::DURING => $this->__('Logging in as admin'),
                AjaxStageInterface::SUCCESS => $this->__('Logged in as admin'),
                AjaxStageInterface::FAIL => $this->__('There was an error logging in as admin')
            ],
            3 => [
                AjaxStageInterface::NAME => 'upgrade_event',
                AjaxStageInterface::PRE => $this->__('Fire upgrade event'),
                AjaxStageInterface::DURING => $this->__('Firing upgrade event'),
                AjaxStageInterface::SUCCESS => $this->__('Fired upgrade event'),
                AjaxStageInterface::FAIL => $this->__('There was an error firing the upgrade event')
            ],
            4 => [
                AjaxStageInterface::NAME => 'upgrademodules',
                AjaxStageInterface::PRE => $this->__('Upgrade modules'),
                AjaxStageInterface::DURING => $this->__('Upgrading modules'),
                AjaxStageInterface::SUCCESS => $this->__('Modules upgraded'),
                AjaxStageInterface::FAIL => $this->__('There was an error upgrading the modules')
            ],
            5 => [
                AjaxStageInterface::NAME => 'regenthemes',
                AjaxStageInterface::PRE => $this->__('Regenerate themes'),
                AjaxStageInterface::DURING => $this->__('Regenerating themes'),
                AjaxStageInterface::SUCCESS => $this->__('Themes regenerated'),
                AjaxStageInterface::FAIL => $this->__('There was an error regenerating the themes')
            ],
            6 => [
                AjaxStageInterface::NAME => 'versionupgrade',
                AjaxStageInterface::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => $this->oldVersion, '%newVersion%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                AjaxStageInterface::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION])
            ],
            7 => [
                AjaxStageInterface::NAME => 'finalizeparameters',
                AjaxStageInterface::PRE => $this->__('Finalize parameters'),
                AjaxStageInterface::DURING => $this->__('Finalizing parameters'),
                AjaxStageInterface::SUCCESS => $this->__('Parameters finalized'),
                AjaxStageInterface::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            8 => [
                AjaxStageInterface::NAME => 'clearcaches',
                AjaxStageInterface::PRE => $this->__('Clear caches'),
                AjaxStageInterface::DURING => $this->__('Clearing caches'),
                AjaxStageInterface::SUCCESS => $this->__('Caches cleared'),
                AjaxStageInterface::FAIL => $this->__('There was an error clearing caches')
            ],
            9 => [
                AjaxStageInterface::NAME => 'finish',
                AjaxStageInterface::PRE => $this->__('Finish'),
                AjaxStageInterface::DURING => $this->__('Finish'),
                AjaxStageInterface::SUCCESS => $this->__('Finish'),
                AjaxStageInterface::FAIL => $this->__('Finish')
            ]
        ]];
    }
}
