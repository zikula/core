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
        return 'ZikulaCoreInstallerBundle:Upgrade:ajaxupgrader.html.twig';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return ['stages' => [
            1 => [
                self::NAME => 'reinitparams',
                self::PRE => $this->__('Reinitialize parameters'),
                self::DURING => $this->__('Reinitializing parameters'),
                self::SUCCESS => $this->__('Reinitialized parameters'),
                self::FAIL => $this->__('There was an error reinitialize parameters')
            ],
            2 => [
                self::NAME => 'loginadmin',
                self::PRE => $this->__('Login'),
                self::DURING => $this->__('Logging in as admin'),
                self::SUCCESS => $this->__('Logged in as admin'),
                self::FAIL => $this->__('There was an error logging in as admin')
            ],
            3 => [
                self::NAME => 'upgrade_event',
                self::PRE => $this->__('Fire upgrade event'),
                self::DURING => $this->__('Firing upgrade event'),
                self::SUCCESS => $this->__('Fired upgrade event'),
                self::FAIL => $this->__('There was an error firing the upgrade event')
            ],
            4 => [
                self::NAME => 'upgrademodules',
                self::PRE => $this->__('Upgrade modules'),
                self::DURING => $this->__('Upgrading modules'),
                self::SUCCESS => $this->__('Modules upgraded'),
                self::FAIL => $this->__('There was an error upgrading the modules')
            ],
            5 => [
                self::NAME => 'regenthemes',
                self::PRE => $this->__('Regenerate themes'),
                self::DURING => $this->__('Regenerating themes'),
                self::SUCCESS => $this->__('Themes regenerated'),
                self::FAIL => $this->__('There was an error regenerating the themes')
            ],
            6 => [
                self::NAME => 'versionupgrade',
                self::PRE => $this->__f('Upgrade from Core %oldVersion% to Core %newVersion%', ['%oldVersion%' => $this->oldVersion, '%newVersion%' => ZikulaKernel::VERSION]),
                self::DURING => $this->__f('Upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                self::SUCCESS => $this->__f('Upgraded to Core %version%', ['%version%' => ZikulaKernel::VERSION]),
                self::FAIL => $this->__f('There was an error upgrading to Core %version%', ['%version%' => ZikulaKernel::VERSION])
            ],
            7 => [
                self::NAME => 'finalizeparameters',
                self::PRE => $this->__('Finalize parameters'),
                self::DURING => $this->__('Finalizing parameters'),
                self::SUCCESS => $this->__('Parameters finalized'),
                self::FAIL => $this->__('There was an error finalizing the parameters')
            ],
            8 => [
                self::NAME => 'clearcaches',
                self::PRE => $this->__('Clear caches'),
                self::DURING => $this->__('Clearing caches'),
                self::SUCCESS => $this->__('Caches cleared'),
                self::FAIL => $this->__('There was an error clearing caches')
            ],
            9 => [
                self::NAME => 'finish',
                self::PRE => $this->__('Finish'),
                self::DURING => $this->__('Finish'),
                self::SUCCESS => $this->__('Finish'),
                self::FAIL => $this->__('Finish')
            ]
        ]];
    }
}
