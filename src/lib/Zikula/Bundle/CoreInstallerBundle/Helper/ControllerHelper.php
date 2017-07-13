<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Zikula\Core\Exception\FatalErrorException;

class ControllerHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ControllerHelper constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * return an array of variables to assign to all installer templates
     *
     * @return array
     */
    public function getTemplateGlobals(StageInterface $currentStage)
    {
        $globals = [
            'version' => ZikulaKernel::VERSION,
            'currentstage' => $currentStage->getName()
        ];

        return array_merge($globals, $currentStage->getTemplateParams());
    }

    /**
     * Set up php for zikula install
     *
     * @throws FatalErrorException if settings are not capable of performing install or sustaining Zikula
     */
    public function initPhp()
    {
        $warnings = [];
        if (version_compare(\PHP_VERSION, '5.6.0', '<') && false === ini_set('mbstring.internal_encoding', 'UTF-8')) {
            // mbstring.internal_encoding is deprecated in php 5.6.0
            $currentSetting = ini_get('mbstring.internal_encoding');
            $warnings[] = $this->translator->__f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                '%1$s' => 'ini_set',
                '%2$s' => 'mbstring.internal_encoding',
                '%3$s' => 'UTF-8',
                '%4$s' => $currentSetting
            ]);
        }
        if (false === ini_set('default_charset', 'UTF-8')) {
            $currentSetting = ini_get('default_charset');
            $warnings[] = $this->translator->__f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                '%1$s' => 'ini_set',
                '%2$s' => 'default_charset',
                '%3$s' => 'UTF-8',
                '%4$s' => $currentSetting
            ]);
        }
        if (false === mb_regex_encoding('UTF-8')) {
            $currentSetting = mb_regex_encoding();
            $warnings[] = $this->translator->__f('Could not set %1$s to the value of %2$s. The install or upgrade process may fail at your current setting of %3$s.', [
                '%1$s' => 'mb_regex_encoding',
                '%2$s' => 'UTF-8',
                '%3$s' => $currentSetting
            ]);
        }
        if (false === ini_set('memory_limit', '128M')) {
            $currentSetting = ini_get('memory_limit');
            $warnings[] = $this->translator->__f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                '%1$s' => 'ini_set',
                '%2$s' => 'memory_limit',
                '%3$s' => '128M',
                '%4$s' => $currentSetting
            ]);
        }
        if (false === ini_set('max_execution_time', 86400)) {
            // 86400 = 24 hours
            $currentSetting = ini_get('max_execution_time');
            if ($currentSetting > 0) {
                // 0 = unlimited time
                $warnings[] = $this->translator->__f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', [
                    '%1$s' => 'ini_set',
                    '%2$s' => 'max_execution_time',
                    '%3$s' => '86400',
                    '%4$s' => $currentSetting
                ]);
            }
        }

        return $warnings;
    }

    public function requirementsMet()
    {
        // several other requirements are checked before Symfony is loaded.
        // @see app/SymfonyRequirements.php
        // @see \Zikula\Bundle\CoreInstallerBundle\Util\ZikulaRequirements::runSymfonyChecks
        $results = [];

        $x = explode('.', str_replace('-', '.', phpversion()));
        $phpVersion = "$x[0].$x[1].$x[2]";
        $results['phpsatisfied'] = version_compare($phpVersion, ZikulaKernel::PHP_MINIMUM_VERSION, ">=");
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
        $results['phpversion'] = phpversion();
        $results['phpcoreminversion'] = ZikulaKernel::PHP_MINIMUM_VERSION;

        return $results;
    }

    /**
     * Write admin credentials to param file as encoded values
     *
     * @param YamlDumper $yamlManager
     * @param array $data
     * @throws AbortStageException
     */
    public function writeEncodedAdminCredentials(YamlDumper $yamlManager, array $data)
    {
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $params = array_merge($yamlManager->getParameters(), $data);
        try {
            $yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException($this->translator->__f('Cannot write parameters to %s file.', ['%s' => 'custom_parameters.yml']));
        }
    }
}
