<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Composer;

/**
 * A class to handle less2css generation by composer
 */
class LessGenerator
{
    /**
     * This function generates from the customized bootstrap.less und font-awesome.less a combined css file
     *
     * @param string|null $writeTo Where to dump the generated file.
     */
    public static function generateCombinedBootstrapFontAwesomeCSS($writeTo = null)
    {
        // Also change build.xml if you change the default writeTo path here!
        $writeTo = is_string($writeTo) ? $writeTo : 'src/web/bootstrap-font-awesome.css';
        $parser = new \Less_Parser();
        $parser->setOptions(array('relativeUrls' => false, 'compress' => true));
        $parser->parseFile('src/style/bootstrap-font-awesome.less');

        file_put_contents($writeTo, $parser->getCss());
    }
}
