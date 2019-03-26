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

namespace Zikula\BlocksModule\Block;

use DOMDocument;
use XSLTProcessor;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Block\Form\Type\XsltBlockType;

/**
 * Block to display a parsed xml document
 */
class XsltBlock extends AbstractBlockHandler
{
    public function display(array $properties)
    {
        if (!$this->hasPermission('xsltblock::', "{$properties[title]}::", ACCESS_OVERVIEW)) {
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
        return XsltBlockType::class;
    }

    public function getFormTemplate()
    {
        return '@ZikulaBlocksModule/Block/xslt_modify.html.twig';
    }
}
