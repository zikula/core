<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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

use Zikula\Core\AbstractBlockHandler;

/**
 * Block to display html 
 */
class HtmlBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered bock
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('HTMLblock::', "$properties[title]::", ACCESS_OVERVIEW)) {
            return '';
        }

        return $properties['content'];
    }

    public function getFormClassName()
    {
        return 'Zikula\BlocksModule\Block\Form\Type\HtmlBlockType';
    }

    public function getFormTemplate()
    {
        return '@ZikulaBlocksModule/Block/html_modify.html.twig';
    }
}
