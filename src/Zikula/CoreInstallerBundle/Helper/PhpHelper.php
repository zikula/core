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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Contracts\Translation\TranslatorInterface;

class PhpHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Set up PHP for Zikula installation.
     */
    public function setUp(): array
    {
        $warnings = [];
        if (false === ini_set('default_charset', 'UTF-8')) {
            $currentSetting = ini_get('default_charset');
            $warnings[] = $this->translator->trans('Could not use %command% to set the %setting% to the value of %requiredValue%. The install or upgrade process may fail at your current setting of %currentValue%.', [
                '%command%' => 'ini_set',
                '%setting%' => 'default_charset',
                '%requiredValue%' => 'UTF-8',
                '%currentValue%' => $currentSetting
            ]);
        }
        if (false === mb_regex_encoding('UTF-8')) {
            $currentSetting = mb_regex_encoding();
            $warnings[] = $this->translator->trans('Could not set %setting% to the value of %requiredValue%. The install or upgrade process may fail at your current setting of %currentValue%.', [
                '%setting%' => 'mb_regex_encoding',
                '%requiredValue%' => 'UTF-8',
                '%currentValue%' => $currentSetting
            ]);
        }
        if (false === ini_set('memory_limit', '128M')) {
            $currentSetting = ini_get('memory_limit');
            $warnings[] = $this->translator->trans('Could not use %command% to set the %setting% to the value of %requiredValue%. The install or upgrade process may fail at your current setting of %currentValue%.', [
                '%command%' => 'ini_set',
                '%setting%' => 'memory_limit',
                '%requiredValue%' => '128M',
                '%currentValue%' => $currentSetting
            ]);
        }
        if (false === ini_set('max_execution_time', '86400')) {
            // 86400 = 24 hours
            $currentSetting = ini_get('max_execution_time');
            if ($currentSetting > 0) {
                // 0 = unlimited time
                $warnings[] = $this->translator->trans('Could not use %command% to set the %setting% to the value of %requiredValue%. The install or upgrade process may fail at your current setting of %currentValue%.', [
                    '%command%' => 'ini_set',
                    '%setting%' => 'max_execution_time',
                    '%requiredValue%' => '86400',
                    '%currentValue%' => $currentSetting
                ]);
            }
        }

        return $warnings;
    }
}
