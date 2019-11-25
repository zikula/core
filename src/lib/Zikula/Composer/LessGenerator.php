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
use Less_Parser;

/**
 * A class to handle less2css generation by composer
 */
class LessGenerator
{
    /**
     * Generates a combined css file from the customized bootstrap.less und font-awesome.less.
     */
    public static function generateCombinedBootstrapFontAwesomeCSS(Event $event): void
    {
        $args = $event->getArguments();
        // Also change build.xml if you change the default writeTo path here!
        $writeTo = isset($args['writeTo']) && is_string($args['writeTo']) ? $args['writeTo'] : 'src/web/bootstrap-font-awesome.css';
        $parser = new Less_Parser();
        $parser->setOptions(['relativeUrls' => false, 'compress' => true]);
        $parser->parseFile('src/web/bundles/core/css/bootstrap-font-awesome.less');

        file_put_contents($writeTo, $parser->getCss());
    }
}
