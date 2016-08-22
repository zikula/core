<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\CoreInstallerBundle\Util\ZikulaRequirements;

/**
 * If not installed, or if currentVersion != installedVersion run requirement checks.
 * Die on failure.
 * @param array $parameters
 */
function requirementCheck($parameters) {
    // on install or upgrade, check if system requirements are met.
    if (($parameters['installed'] == false)
        || (!empty($parameters[\Zikula_Core::CORE_INSTALLED_VERSION_PARAM])
            && version_compare($parameters[\Zikula_Core::CORE_INSTALLED_VERSION_PARAM], \Zikula_Core::VERSION_NUM, '<'))) {
        $versionChecker = new ZikulaRequirements();
        $versionChecker->runSymfonyChecks();
        if (!empty($versionChecker->requirementsErrors)) {
            foreach ($versionChecker->requirementsErrors as $error) {
                echo $error . '<br/>';
            }
            die();
        }
    }
}
