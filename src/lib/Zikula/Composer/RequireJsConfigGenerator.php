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

/**
 * A class to rewrite RequireJS configuration
 */
class RequireJsConfigGenerator
{
    /**
     * This function generates from the customized bootstrap.less und font-awesome.less a combined css file
     *
     * @param string|null Where to dump the generated file
     */
    public static function regenerateRequireJs(Event $event)
    {
            
        // Retrieve basic information about the environment and present a
        // message to the user.
        $composer = $event->getComposer();
        $io = $event->getIO();
        $io->write('<info>Compiling component files</info>');

        // Set up all the processes.
        $processes = array(
            // Copy the assets to the Components directory.
//            "ComponentInstaller\\Process\\CopyProcess",
            // Build the require.js file.
            "Zikula\\Composer\\Process\\RequireJsProcess",
            // Build the require.css file.
//            "ComponentInstaller\\Process\\RequireCssProcess",
            // Compile the require-built.js file.
            "ComponentInstaller\\Process\\BuildJsProcess",
        );

        // Initialize and execute each process in sequence.
        foreach ($processes as $class) {
            if(!class_exists($class)){
                $io->write("<warning>Process class '$class' not found, skipping this process</warning>");
                continue;
            }
            $io->write("<info>Running '$class' </info>");
            /** @var \ComponentInstaller\Process\Process $process */
            $process = new $class($composer, $io);
            // When an error occurs during initialization, end the process.
            if (!$process->init()) {
                $io->write("<warning>An error occurred while initializing the '$class' process.</warning>");
                break;
            }
            $process->process();
        }
        
    }
}
