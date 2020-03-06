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

use ComponentInstaller\Process\BuildJsProcess;
use ComponentInstaller\Process\Process;
use Composer\Script\Event;
use RuntimeException;
use Zikula\ExtensionsModule\Composer\Process\RequireJsProcess;

/**
 * A class to rewrite RequireJS configuration
 */
class RequireJsConfigGenerator
{
    /**
     * Generates a RequireJS configuration file.
     */
    public static function regenerateRequireJs(Event $event): void
    {
        // Retrieve basic information about the environment and present a
        // message to the user.
        $composer = $event->getComposer();
        $io = $event->getIO();
        $io->write('<info>Compiling component files</info>');

        // Set up all the processes.
        $processes = [
            // Build the require.js file.
            RequireJsProcess::class,
            // Compile the require-built.js file.
            BuildJsProcess::class,
        ];

        // Initialize and execute each process in sequence.
        foreach ($processes as $class) {
            if (!class_exists($class)) {
                $io->write("<warning>Process class '${class}' not found, skipping this process</warning>");
                continue;
            }
            $io->write("<info>Running '${class}' </info>");
            /** @var Process $process */
            $process = new $class($composer, $io);
            // When an error occurs during initialization, end the process.
            if (!$process->init()) {
                $io->write("<warning>An error occurred while initializing the '${class}' process.</warning>");
                break;
            }
            $process->process();
        }

        // move files into subfolder
        $publicDir = $composer->getConfig()->get('public-dir') . '/';
        $requireDir = $publicDir . 'require/';
        if (!file_exists($requireDir) && !mkdir($requireDir, 0755) && !is_dir($requireDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $requireDir));
        }
        $requireJsFiles = [
            'require.config.js',
            'require.css',
            'require.js',
            'require-built.js'
        ];
        foreach ($requireJsFiles as $fileName) {
            if (!file_exists($publicDir . $fileName)) {
                continue;
            }
            rename($publicDir . $fileName, $requireDir . $fileName);
        }
    }
}
