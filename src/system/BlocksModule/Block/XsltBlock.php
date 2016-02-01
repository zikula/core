<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Block;

use DOMDocument;
use XSLTProcessor;
use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * Block to display a parsed xml document
 */
class XsltBlock extends AbstractBlockHandler
{
    public function display(array $properties)
    {
        if (!$this->hasPermission('xsltblock::', "$properties[title]::", ACCESS_OVERVIEW)) {
            return '';
        }

        $doc = new DOMDocument();
        $xsl = new XSLTProcessor();

        // load stylesheet
        if (isset($properties['styleurl']) && !empty($properties['styleurl'])) {
            $doc->load($properties['styleurl']);
        } else {
            $doc->loadXML($properties['stylecontents']);
        }
        $xsl->importStyleSheet($doc);

        // load xml source
        if (isset($properties['docurl']) && !empty($properties['docurl'])) {
            $doc->load($properties['docurl']);
        } else {
            $doc->loadXML($properties['doccontents']);
        }

        // apply stylesheet and return output
        return $xsl->transformToXML($doc);
    }

    public function getFormClassName()
    {
        return 'Zikula\BlocksModule\Block\Form\Type\XsltBlockType';
    }

    public function getFormTemplate()
    {
        return '@ZikulaBlocksModule/Block/xslt_modify.html.twig';
    }
}
