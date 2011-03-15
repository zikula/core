<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View modifier to apply a xml stylesheet to a variable
 *
 * The modifier requires php 5's DOM and XSLT funtionality
 *
 * Example
 *
 *   {$myvar|xslt:'your_xsl_file.xsl'}
 *
 * @param array  $string   The contents to transform.
 * @param string $styleurl Url to XSL file.
 *
 * @see    modifier.xslt.php::smarty_modifier_xslt
 * @return string The modified output.
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
