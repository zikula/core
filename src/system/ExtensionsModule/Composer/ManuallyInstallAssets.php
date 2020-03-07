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
    protected static $assets = [
        '/frdh/mmenu.js/dist/mmenu.js' => '/mmenu/js/mmenu.js',
        '/frdh/mmenu.js/dist/mmenu.css' => '/mmenu/css/mmenu.css',
        '/itsjavi/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.css' => '/fontawesome-iconpicker/fontawesome-iconpicker.css',
        '/itsjavi/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css' => '/fontawesome-iconpicker/fontawesome-iconpicker.min.css',
        '/itsjavi/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js' => '/fontawesome-iconpicker/fontawesome-iconpicker.js',
        '/itsjavi/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js' => '/fontawesome-iconpicker/fontawesome-iconpicker.min.js',
        '/dimsemenov/magnific-popup/dist/jquery.magnific-popup.js' => '/magnific-popup/jquery.magnific-popup.js',
        '/dimsemenov/magnific-popup/dist/jquery.magnific-popup.min.js' => '/magnific-popup/jquery.magnific-popup.min.js',
        '/dimsemenov/magnific-popup/dist/magnific-popup.css' => '/magnific-popup/magnific-popup.css',
    ];

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
    }
}
