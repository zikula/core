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

namespace Zikula\Composer;

use Composer\Script\Event;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;
use Symfony\Component\Filesystem\Filesystem;

class CopyRequirementsFile extends ScriptHandler
{
    public static function copy(Event $event): void
    {
        $options = static::getOptions($event);
        $fs = new Filesystem();

        $varDir = $options['symfony-var-dir'];
        if (!static::hasDirectory($event, 'symfony-var-dir', $varDir, 'install the requirements files')) {
            return;
        }
        $fs->copy(__DIR__ . '/../../../vendor/sensio/distribution-bundle/Resources/skeleton/app/SymfonyRequirements.php', $varDir . '/SymfonyRequirements.php', true);
    }
}
