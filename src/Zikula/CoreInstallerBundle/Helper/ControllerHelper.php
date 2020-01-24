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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\StageInterface;

class ControllerHelper
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
     * Return an array of variables to assign to all installer templates.
     */
    public function getTemplateGlobals(StageInterface $currentStage): array
    {
        $globals = [
            'version' => ZikulaKernel::VERSION,
            'currentstage' => $currentStage->getName()
        ];

        return array_merge($globals, $currentStage->getTemplateParams());
    }

    /**
     * Set up PHP for Zikula installation.
     */
    public function initPhp(): array
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

    /**
     * @return array|bool
     */
    public function requirementsMet()
    {
        // several other requirements are checked before Symfony is loaded.
        // @see app/SymfonyRequirements.php
        // @see \Zikula\Bundle\CoreInstallerBundle\Util\ZikulaRequirements::runSymfonyChecks
        $results = [];

        $x = explode('.', str_replace('-', '.', PHP_VERSION));
        $phpVersion = "{$x[0]}.{$x[1]}.{$x[2]}";
        $results['phpsatisfied'] = version_compare($phpVersion, ZikulaKernel::PHP_MINIMUM_VERSION, '>=');
        $results['pdo'] = extension_loaded('pdo');
        $supportsUnicode = preg_match('/^\p{L}+$/u', 'TheseAreLetters');
        $results['pcreUnicodePropertiesEnabled'] = (isset($supportsUnicode) && (bool)$supportsUnicode);
        $requirementsMet = true;
        foreach ($results as $check) {
            if (!$check) {
                $requirementsMet = false;
                break;
            }
        }
        if ($requirementsMet) {
            return true;
        }
        $results['phpversion'] = PHP_VERSION;
        $results['phpcoreminversion'] = ZikulaKernel::PHP_MINIMUM_VERSION;

        return $results;
    }

    /**
     * Write admin credentials to param file as encoded values.
     *
     * @throws AbortStageException
     */
    public function writeEncodedAdminCredentials(YamlDumper $yamlManager, array $data = []): void
    {
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $params = array_merge($yamlManager->getParameters(), $data);
        try {
            $yamlManager->setParameters($params);
        } catch (IOException $exception) {
            throw new AbortStageException($this->translator->trans('Cannot write parameters to %fileName% file.', ['%fileName%' => 'services_custom.yaml']));
        }
    }
}
