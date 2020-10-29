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

namespace Zikula\Bundle\CoreInstallerBundle\Util;

use Symfony\Component\Yaml\Yaml;
use Zikula\Bundle\CoreBundle\DependencyInjection\Configuration;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class RequirementChecker
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $installedVersion;

    public function __construct(string $installed)
    {
        $this->installedVersion = $installed;
    }

    /**
     * If not installed, or if currentVersion != installedVersion run
     * requirement checks. Die on failure.
     */
    public function verify(): void
    {
        // on install or upgrade, check if system requirements are met.
        if (version_compare($this->installedVersion, ZikulaKernel::VERSION, '<')) {
            $this->loadParametersFromFile();
            $versionChecker = new ZikulaRequirements();
            $versionChecker->runSymfonyChecks($this->parameters);
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
            exit();
        }
    }

    private function loadParametersFromFile(): void
    {
        if (0 < count($this->parameters)) {
            return;
        }
        $projectDir = dirname(__DIR__, 4); // should work when Bundle in vendor too
        $kernelConfig = Yaml::parse(file_get_contents(realpath($projectDir . '/config/services.yaml')));
        if (is_readable($file = $projectDir . '/config/packages/core.yaml')) {
            $contents = Yaml::parse(file_get_contents($file));
            $kernelConfig['parameters']['datadir'] = $contents['core']['datadir'] ?? Configuration::DEFAULT_DATADIR;
        } else {
            $kernelConfig['parameters']['datadir'] = Configuration::DEFAULT_DATADIR;
        }
        $parameters = $kernelConfig['parameters'];
        $parameters['kernel.project_dir'] = $projectDir;

        $this->parameters = $parameters;
    }
}
