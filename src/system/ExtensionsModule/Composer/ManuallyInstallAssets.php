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

namespace Zikula\ExtensionsModule\Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ManuallyInstallAssets
 *
 * Manually install vendor assets to a defined path in the web directory.
 */
class ManuallyInstallAssets
{
    /**
     * @var array
     * The list of assets. [[vendorPath => destinationPath]]
     */
    protected static $assets = [];

    public static function install(Event $event): void
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $publicDir = $extra['public-dir'] ?? 'public';
        if (!is_dir($publicDir)) {
            $event->getIO()->write(sprintf('The %s (%s) specified in composer.json was not found in %s, can not %s.', 'public-dir', $publicDir, getcwd(), 'manually install assets'));

            return;
        }
        $config = $event->getComposer()->getConfig();
        $vendorDir = $config->has('vendor-dir') ? $config->get('vendor-dir') : 'vendor';
        if (!is_dir($vendorDir)) {
            $event->getIO()->write(sprintf('The %s (%s) specified in composer.json was not found in %s, can not %s.', 'vendor-dir', $vendorDir, getcwd(), 'manually install assets'));

            return;
        }
        $fs = new Filesystem();
        $event->getIO()->write('<info>Zikula manually installing assets:</info>');
        foreach (static::$assets as $assetPath => $destinationPath) {
            $fs->copy($vendorDir . $assetPath, $publicDir . $destinationPath, true);
            $event->getIO()->write(sprintf('Zikula installed <comment>%s</comment> in <comment>%s</comment>', $assetPath, $publicDir . $destinationPath));
        }
        $cwd = getcwd();
        $fs->symlink($cwd . '/' . $publicDir . '/jqueryui', $cwd . '/' . $publicDir . '/jquery-ui');
        $event->getIO()->write(sprintf('Zikula symlinked <comment>%s</comment> to <comment>%s</comment>', $publicDir . '/jqueryui', $publicDir . '/jquery-ui'));
    }
}
