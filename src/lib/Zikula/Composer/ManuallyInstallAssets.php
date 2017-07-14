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

/**
 * Class ManuallyInstallAssets
 *
 * Manually install vendor assets to a defined path in the web directory.
 */
class ManuallyInstallAssets extends ScriptHandler
{
    /**
     * @var array
     * The list of assets. [[vendorPath => destinationPath]]
     */
    protected static $assets = [
        '/jQuery.mmenu/dist/jquery.mmenu.all.js' => '/jquery-mmenu/js/jquery.mmenu.all.js',
        '/jQuery.mmenu/dist/jquery.mmenu.all.css' => '/jquery-mmenu/css/jquery.mmenu.all.css',
        '/dimsemenov/magnific-popup/dist/jquery.magnific-popup.js' => '/magnific-popup/jquery.magnific-popup.js',
        '/dimsemenov/magnific-popup/dist/jquery.magnific-popup.min.js' => '/magnific-popup/jquery.magnific-popup.min.js',
        '/dimsemenov/magnific-popup/dist/magnific-popup.css' => '/magnific-popup/magnific-popup.css',
    ];

    public static function install(Event $event)
    {
        $options = static::getOptions($event);
        $webDir = $options['symfony-web-dir'];
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        if (!static::hasDirectory($event, 'symfony-web-dir', $webDir, 'manually install assets')) {
            return;
        }
        if (!static::hasDirectory($event, 'vendor-dir', $vendorDir, 'manually install assets')) {
            return;
        }
        $fs = new Filesystem();
        $event->getIO()->write('<info>Zikula manually installing assets:</info>');
        foreach (static::$assets as $assetPath => $destinationPath) {
            $fs->copy($vendorDir . $assetPath, $webDir . $destinationPath, true);
            $event->getIO()->write(sprintf('Zikula installed <comment>%s</comment> in <comment>%s</comment>', $assetPath, $webDir . $destinationPath));
        }
    }
}
