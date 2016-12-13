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
 * Block to display simple rendered text
 */
class TextBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered block
     */
    public function display(array $properties)
    {
        $title = (!empty($properties['title'])) ? $properties['title'] : '';
        if ((!$this->hasPermission('Textblock::', "$title::", ACCESS_OVERVIEW))
        || (!$this->hasPermission('Textblock::bid', "::$properties[bid]", ACCESS_OVERVIEW))) {
            return '';
        }

        return $this->renderView('@ZikulaBlocksModule/Block/textblock.html.twig', [
            'content' => nl2br($properties['content'])
        ]);
    }

    public function getFormClassName()
    {
        return 'Zikula\BlocksModule\Block\Form\Type\TextBlockType';
    }
}
