<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Component\Wizard\StageInterface;

class ControllerUtil
{
    /**
     * return an array of variables to assign to all installer templates
     *
     * @return array
     */
    public function getTemplateGlobals(StageInterface $currentStage)
    {
        $globals = array(
            'lang' => \ZLanguage::getLanguageCode(),
            'charset' => \ZLanguage::getEncoding(),
            'version' => \Zikula_Core::VERSION_NUM,
            'currentstage' => $currentStage->getName(),
        );

        return array_merge($globals, $currentStage->getTemplateParams());
    }

    /**
     * Set up php for zikula install
     *
     * @throws FatalErrorException if settings are not capable of performing install or sustaining Zikula
     */
    public function initPhp()
    {
        $warnings = array();
        if (!function_exists('mb_get_info')) {
            $warnings[] = __('mbstring is not installed in PHP.  Zikula cannot install or upgrade without this extension.');
        }
        if (ini_set('mbstring.internal_encoding', 'UTF-8') === false) {
            // mbstring.internal_encoding is deprecated in php 5.6.0
            $currentSetting = ini_get('mbstring.internal_encoding');
            $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', array('ini_set', 'mbstring.internal_encoding', 'UTF-8', $currentSetting));
        }
        if (ini_set('default_charset', 'UTF-8') === false) {
            $currentSetting = ini_get('default_charset');
            $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', array('ini_set', 'default_charset', 'UTF-8', $currentSetting));
        }
        if (mb_regex_encoding('UTF-8') === false) {
            $currentSetting = mb_regex_encoding();
            $warnings[] = __f('Could not set %1$s to the value of %2$s. The install or upgrade process may fail at your current setting of %3$s.', array('mb_regex_encoding', 'UTF-8', $currentSetting));
        }
        if (ini_set('memory_limit', '128M') === false) {
            $currentSetting = ini_get('memory_limit');
            $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', array('ini_set', 'memory_limit', '128M', $currentSetting));
        }
        if (ini_set('max_execution_time', 86400) === false) {
            // 86400 = 24 hours
            $currentSetting = ini_get('max_execution_time');
            if ($currentSetting > 0) {
                // 0 = unlimited time
                $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The install or upgrade process may fail at your current setting of %4$s.', array('ini_set', 'max_execution_time', '86400', $currentSetting));
            }
        }

        return $warnings;
    }

    public function requirementsMet(ContainerInterface $container)
    {
        $results = array();

        $x = explode('.', str_replace('-', '.', phpversion()));
        $phpVersion = "$x[0].$x[1].$x[2]";
        $results['phpsatisfied'] = version_compare($phpVersion, \Zikula_Core::PHP_MINIMUM_VERSION, ">=");
        $results['phpsatisfied'] = $results['phpsatisfied'] && !version_compare($phpVersion, '5.3.16', "=="); // 5.3.16 is known to not work

        $results['datetimezone'] = ini_get('date.timezone');
        $results['pdo'] = extension_loaded('pdo');
        $results['register_globals'] = !ini_get('register_globals');
        $results['magic_quotes_gpc'] = !ini_get('magic_quotes_gpc');
        $results['phptokens'] = function_exists('token_get_all');
        $results['mbstring'] = function_exists('mb_get_info');
        $isEnabled = @preg_match('/^\p{L}+$/u', 'TheseAreLetters');
        $results['pcreUnicodePropertiesEnabled'] = (isset($isEnabled) && (bool)$isEnabled);
        $results['json_encode'] = function_exists('json_encode');
//        $results['config_personal_config_php'] = !is_writable('config/personal_config.php');
//        $results['custom_parameters_yml'] = !is_writable('app/config/custom_parameters.yml');
        $datadir = $container->getParameter('datadir');
        $files = array(
//            'config/config.php',
            'app/cache/',
//            'app/config/parameters.yml',
            "$datadir/",
            'app/config/',
            'app/config/dynamic'
        );
        foreach ($files as $file) {
            $results[$file] = is_writable($file);
        }
        $requirementsMet = true;
        foreach ($results as $check) {
            if (!$check) {
                $requirementsMet = false;
                break;
            }
        }
        $results['phpversion'] = phpversion();
        if ($requirementsMet) {

            return true;
        }

        return $results;
    }
}