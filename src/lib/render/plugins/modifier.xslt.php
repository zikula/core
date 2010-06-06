<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty modifier to apply a xml stylesheet to a variable
 *
 * The modifier requires php 5's DOM and XSLT funtionality
 *
 * Example
 *
 *   {$myvar|xslt:'your_xsl_file.xsl'}
 *
 * @see          modifier.xslt.php::smarty_modifier_xslt
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_xslt($string, $styleurl)
{
    // create new objects
    $doc = new DOMDocument();
    $xsl = new XSLTProcessor();

    // check for the stylesheet parameter
    if (!isset($styleurl) || empty($styleurl)) {
        return $string;
    }

    // load and import stylesheet
    $doc->load($styleurl);
    $xsl->importStyleSheet($doc);

    // load xml source
    $doc->loadXML($string);

    // apply stylesheet and return output
    return $xsl->transformToXML($doc);
}
