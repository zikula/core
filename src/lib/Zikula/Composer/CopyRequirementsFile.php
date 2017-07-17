<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Composer;

use Composer\Script\Event;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;
use Symfony\Component\Filesystem\Filesystem;

class CopyRequirementsFile extends ScriptHandler
{
    public static function copy(Event $event)
    {
        $options = static::getOptions($event);
        $appDir = $options['symfony-app-dir'];
        $fs = new Filesystem();
        $newDirectoryStructure = static::useNewDirectoryStructure($options);

        if (!$newDirectoryStructure) {
            // for Core-1.x
            if (!static::hasDirectory($event, 'symfony-app-dir', $appDir, 'install the requirements files')) {
                return;
            }
            $fs->copy(__DIR__ . '/../../../vendor/sensio/distribution-bundle/Resources/skeleton/app/SymfonyRequirements.php', $appDir . '/SymfonyRequirements.php', true);
        } else {
            // for Core-2.x
            $varDir = $options['symfony-var-dir'];
            if (!static::hasDirectory($event, 'symfony-var-dir', $varDir, 'install the requirements files')) {
                return;
            }
            $fs->copy(__DIR__ . '/../../../vendor/sensio/distribution-bundle/Resources/skeleton/app/SymfonyRequirements.php', $varDir . '/SymfonyRequirements.php', true);
        }
    }
}
