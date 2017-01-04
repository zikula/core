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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Symfony\Component\Filesystem\Exception\IOException;
use Zikula\Component\Wizard\AbortStageException;

class ControllerHelper
{
    /**
     * return an array of variables to assign to all installer templates
     *
     * @return array
     */
    public function getTemplateGlobals(StageInterface $currentStage)
    {
        $globals = [
            'version' => \ZikulaKernel::VERSION,
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
            $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', ['ini_set', 'mbstring.internal_encoding', 'UTF-8', $currentSetting]);
        }
        if (false === ini_set('default_charset', 'UTF-8')) {
            $currentSetting = ini_get('default_charset');
            $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', ['ini_set', 'default_charset', 'UTF-8', $currentSetting]);
        }
        if (false === mb_regex_encoding('UTF-8')) {
            $currentSetting = mb_regex_encoding();
            $warnings[] = __f('Could not set %1$s to the value of %2$s. The install or upgrade process may fail at your current setting of %3$s.', ['mb_regex_encoding', 'UTF-8', $currentSetting]);
        }
        if (false === ini_set('memory_limit', '128M')) {
            $currentSetting = ini_get('memory_limit');
            $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', ['ini_set', 'memory_limit', '128M', $currentSetting]);
        }
        if (false === ini_set('max_execution_time', 86400)) {
            // 86400 = 24 hours
            $currentSetting = ini_get('max_execution_time');
            if ($currentSetting > 0) {
                // 0 = unlimited time
                $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', ['ini_set', 'max_execution_time', '86400', $currentSetting]);
            }
        }

        return $warnings;
    }

    public function requirementsMet(ContainerInterface $container)
    {
        // several other requirements are checked before Symfony is loaded.
        // @see app/SymfonyRequirements.php
        // @see \Zikula\Bundle\CoreInstallerBundle\Util\ZikulaRequirements::runSymfonyChecks
        $results = [];

        $x = explode('.', str_replace('-', '.', phpversion()));
        $phpVersion = "$x[0].$x[1].$x[2]";
        $results['phpsatisfied'] = version_compare($phpVersion, \ZikulaKernel::PHP_MINIMUM_VERSION, ">=");

        $results['pdo'] = extension_loaded('pdo');
        $isEnabled = @preg_match('/^\p{L}+$/u', 'TheseAreLetters');
        $results['pcreUnicodePropertiesEnabled'] = (isset($isEnabled) && (bool)$isEnabled);
        $rootDir = $container->get('kernel')->getRootDir();
        if ($container->hasParameter('upgrading') && $container->getParameter('upgrading') === true) {
            $files = [
                'personal_config' => '/../config/personal_config.php',
                'custom_parameters' => '/config/custom_parameters.yml'
            ];
            foreach ($files as $key => $file) {
                $path = realpath($rootDir . $file);
                if ($path === false) {
                    $results[$key] = false;
                } else {
                    $results[$key] = is_writable($path);
                }
            }
        }
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
        $results['phpcoreminversion'] = \ZikulaKernel::PHP_MINIMUM_VERSION;

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
            throw new AbortStageException(__f('Cannot write parameters to %s file.', 'custom_parameters.yml'));
        }
    }

    /**
     * @TODO unused at the moment. probably is needed in the controller to display translations?
     * Load the right language.
     *
     * @return string
     */
    public function setupLang(ContainerInterface $container)
    {
        $lang = 'en';
        // @TODO read this from parameters, not ini
        if (is_readable('config/installer.ini')) {
            $ini = parse_ini_file('config/installer.ini');
            $lang = isset($ini['language']) ? $ini['language'] : 'en';
        }

        // setup multilingual
        $GLOBALS['ZConfig']['System']['language_i18n'] = $lang;
        $GLOBALS['ZConfig']['System']['multilingual'] = true;
        $GLOBALS['ZConfig']['System']['languageurl'] = true;
        $GLOBALS['ZConfig']['System']['language_detect'] = false;
        $container->loadArguments($GLOBALS['ZConfig']['System']);

        $zLang = \ZLanguage::getInstance();
        $zLang->setup($container->get('request'));
    }
}
