<?php

namespace Zikula\Composer;

/**
 * A class to handle less2css generation by composer
 */
class LessGenerator {

    /**
     * This function generates from the customized bootstrap.less und font-awesome.less a combined css file
     *
     * @throws \Exception
     */
    static function generateCombinedBootstrapFontAwesomeCSS() {

        $parser = new \Less_Parser();
        $parser->setOptions(array('relativeUrls' => false, 'compress' => true));
        $parser->parseFile('src/style/bootstrap-font-awesome.less');

        file_put_contents('src/web/bootstrap-font-awesome.css', $parser->getCss());
    }

}