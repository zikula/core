<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Util\ZikulaRequirements;

class RequirementChecker
{
    /**
     * If not installed, or if currentVersion != installedVersion run requirement checks.
     * Die on failure.
     * @param array $parameters
     */
    public function verify($parameters)
    {
        // on install or upgrade, check if system requirements are met.
        if ((false === $parameters['installed'])
            || (!empty($parameters[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM])
                && version_compare($parameters[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM], ZikulaKernel::VERSION, '<'))) {
            $versionChecker = new ZikulaRequirements();
            $versionChecker->runSymfonyChecks($parameters);
            if (empty($versionChecker->requirementsErrors)) {
                return;
            }

            // formatting for both HTML and CLI display
            if ('cli' != php_sapi_name()) {
                echo '<html><body><pre>';
            }
            echo 'The following errors were discovered when checking the' . PHP_EOL . 'Zikula Core system/environment requirements:' . PHP_EOL;
            echo '******************************************************' . PHP_EOL . PHP_EOL;
            foreach ($versionChecker->requirementsErrors as $error) {
                echo $error . PHP_EOL;
            }
            if ('cli' != php_sapi_name()) {
                echo '</pre></body></html>';
            }
            die();
        }
    }
}
