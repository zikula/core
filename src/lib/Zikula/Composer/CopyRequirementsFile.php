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
use Symfony\Component\Filesystem\Filesystem;

/**
 * A class to rewrite RequireJS configuration
 */
class CopyRequirementsFile
{
    /**
     * This function generates from the customized bootstrap.less und font-awesome.less a combined css file
     *
     * @param string|null Where to dump the generated file
     */
    public static function copy(Event $event)
    {
        $fs = new Filesystem();
        $fs->copy(__DIR__.'/../../../vendor/sensio/distribution-bundle/Resources/skeleton/app/SymfonyRequirements.php', __DIR__.'/../../../app/SymfonyRequirements.php');
    }
}
