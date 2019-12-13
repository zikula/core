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

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Component\Wizard\StageInterface;

class AjaxUpgraderStage implements StageInterface
{
    use TranslatorTrait;

    /**
     * @var string
     */
    private $oldVersion;

    public function __construct(TranslatorInterface $translator, string $oldVersion)
    {
        $this->setTranslator($translator);
        $this->oldVersion = $oldVersion;
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
                StageInterface::NAME => 'reinitparams',
                StageInterface::PRE => $this->__('Reinitialize parameters'),
                StageInterface::DURING => $this->__('Reinitializing parameters'),
                StageInterface::SUCCESS => $this->__('Reinitialized parameters'),
                StageInterface::FAIL => $this->__('There was an error reinitialize parameters')
            ],
            2 => [
                StageInterface::NAME => 'loginadmin',
                StageInterface::PRE => $this->__('Login'),
                StageInterface::DURING => $this->__('Logging in as admin'),
                StageInterface::SUCCESS => $this->__('Logged in as admin'),
                StageInterface::FAIL => $this->__('There was an error logging in as admin')
            ],
            3 => [
                StageInterface::NAME => 'upgrade_event',
                StageInterface::PRE => $this->__('Fire upgrade event'),
                StageInterface::DURING => $this->__('Firing upgrade event'),
                StageInterface::SUCCESS => $this->__('Fired upgrade event'),
                StageInterface::FAIL => $this->__('There was an error firing the upgrade event')
            ],
            4 => [
                StageInterface::NAME => 'upgrademodules',
                StageInterface::PRE => $this->__('Upgrade modules'),
                StageInterface::DURING => $this->__('Upgrading modules'),
                StageInterface::SUCCESS => $this->__('Modules upgraded'),
                StageInterface::FAIL => $this->__('There was an error upgrading the modules')
            ],
            5 => [
                StageInterface::NAME => 'regenthemes',
                StageInterface::PRE => $this->__('Regenerate themes'),
                StageInterface::DURING => $this->__('Regenerating themes'),
                StageInterface::SUCCESS => $this->__('Themes regenerated'),
                StageInterface::FAIL => $this->__('There was an error regenerating the themes')
            ],
            6 => [
                StageInterface::NAME => 'versionupgrade',
                StageInterface::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => $this->oldVersion, '%newVersion%' => ZikulaKernel::VERSION]),
                StageInterface::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                StageInterface::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                StageInterface::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION])
            ],
            7 => [
                StageInterface::NAME => 'finalizeparameters',
                StageInterface::PRE => $this->__('Finalize parameters'),
                StageInterface::DURING => $this->__('Finalizing parameters'),
                StageInterface::SUCCESS => $this->__('Parameters finalized'),
                StageInterface::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            8 => [
                StageInterface::NAME => 'clearcaches',
                StageInterface::PRE => $this->__('Clear caches'),
                StageInterface::DURING => $this->__('Clearing caches'),
                StageInterface::SUCCESS => $this->__('Caches cleared'),
                StageInterface::FAIL => $this->__('There was an error clearing caches')
            ],
            9 => [
                StageInterface::NAME => 'finish',
                StageInterface::PRE => $this->__('Finish'),
                StageInterface::DURING => $this->__('Finish'),
                StageInterface::SUCCESS => $this->__('Finish'),
                StageInterface::FAIL => $this->__('Finish')
            ]
        ]];
    }
}
