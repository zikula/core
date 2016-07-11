<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;

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
        $title = (!empty($properties['title'])) ? $properties['title'] : '';
        if (!$this->hasPermission('HTMLblock::', "$title::", ACCESS_OVERVIEW)) {
            return '';
        }

        return '<div>' . $properties['content'] . '</div>';
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
