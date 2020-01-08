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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Common\Translator\TranslatorInterface;
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
            $warnings[] = $this->translator->trans('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                '%1$s' => 'ini_set',
                '%2$s' => 'default_charset',
                '%3$s' => 'UTF-8',
                '%4$s' => $currentSetting
            ]);
        }
        if (false === mb_regex_encoding('UTF-8')) {
            $currentSetting = mb_regex_encoding();
            $warnings[] = $this->translator->trans('Could not set %1$s to the value of %2$s. The install or upgrade process may fail at your current setting of %3$s.', [
                '%1$s' => 'mb_regex_encoding',
                '%2$s' => 'UTF-8',
                '%3$s' => $currentSetting
            ]);
        }
        if (false === ini_set('memory_limit', '128M')) {
            $currentSetting = ini_get('memory_limit');
            $warnings[] = $this->translator->trans('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                '%1$s' => 'ini_set',
                '%2$s' => 'memory_limit',
                '%3$s' => '128M',
                '%4$s' => $currentSetting
            ]);
        }
        if (false === ini_set('max_execution_time', '86400')) {
            // 86400 = 24 hours
            $currentSetting = ini_get('max_execution_time');
            if ($currentSetting > 0) {
                // 0 = unlimited time
                $warnings[] = $this->translator->trans('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                    '%1$s' => 'ini_set',
                    '%2$s' => 'max_execution_time',
                    '%3$s' => '86400',
                    '%4$s' => $currentSetting
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
            throw new AbortStageException($this->translator->trans('Cannot write parameters to %s file.', ['%s' => 'custom_parameters.yml']));
        }
    }
}
