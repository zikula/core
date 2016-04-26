<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View modifier to apply a xml stylesheet to a variable
 *
 * The modifier requires php 5's DOM and XSLT funtionality
 *
 * Example
 *
 *   {$myVar|xslt:'your_xsl_file.xsl'}
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
