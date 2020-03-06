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

namespace Zikula\Bundle\CoreInstallerBundle\Util;

use Symfony\Component\Yaml\Yaml;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class RequirementChecker
{
    private static $parameters;

    /**
     * If not installed, or if currentVersion != installedVersion run
     * requirement checks. Die on failure.
     */
    public static function verify(): void
    {
        self::loadParametersFromFile();

        // on install or upgrade, check if system requirements are met.
        if ((false === self::$parameters['installed'])
            || (!empty(self::$parameters[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM])
                && version_compare(self::$parameters[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM], ZikulaKernel::VERSION, '<'))) {
            $versionChecker = new ZikulaRequirements();
            $versionChecker->runSymfonyChecks(self::$parameters);
            if (empty($versionChecker->requirementsErrors)) {
                return;
            }

            // formatting for both HTML and CLI display
            if ('cli' !== PHP_SAPI) {
                echo '<html><body><pre>';
            }
            echo 'The following errors were discovered when checking the' . PHP_EOL . 'Zikula Core system/environment requirements:' . PHP_EOL;
            echo '******************************************************' . PHP_EOL . PHP_EOL;
            foreach ($versionChecker->requirementsErrors as $error) {
                echo $error . PHP_EOL;
            }
            if ('cli' !== PHP_SAPI) {
                echo '</pre></body></html>';
            }
            die();
        }
    }

    public static function getParameter($name)
    {
        self::loadParametersFromFile();

        return self::$parameters[$name];
    }

    private static function loadParametersFromFile(): void
    {
        if (is_array(self::$parameters)) {
            return;
        }
        $projectDir = dirname(__DIR__, 4); // should work when Bundle in vendor too
        $kernelConfig = Yaml::parse(file_get_contents(realpath($projectDir . '/config/services.yaml')));
        if (is_readable($file = $projectDir . '/config/services_custom.yaml')) {
            $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
        }
        $parameters = $kernelConfig['parameters'];
        $parameters['kernel.project_dir'] = $projectDir;

        self::$parameters = $parameters;
    }
}
