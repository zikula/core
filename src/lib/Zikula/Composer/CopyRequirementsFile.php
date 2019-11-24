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
use Symfony\Component\Filesystem\Filesystem;

class CopyRequirementsFile
{
    public static function copy(Event $event): void
    {
        $varDir = $event->getComposer()->getPackage()->getExtra()['symfony-var-dir'];

        if (empty($varDir) || !is_dir($varDir)) {
            $event->getIO()->write(sprintf('The %s (%s) specified in composer.json was not found in %s, can not %s.', 'symfony-var-dir', $varDir, getcwd(), 'install the requirements files'));
            return;
        }
        $fs = new Filesystem();
        $fs->copy(__DIR__ . '/../../../vendor/symfony/requirements-checker/src/SymfonyRequirements.php', $varDir . '/SymfonyRequirements.php', true);
    }
}
