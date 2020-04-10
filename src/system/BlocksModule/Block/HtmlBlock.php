<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Block\Form\Type\HtmlBlockType;

/**
 * Block to display html.
 */
class HtmlBlock extends AbstractBlockHandler
{
    public function display(array $properties): string
    {
        $title = !empty($properties['title']) ? $properties['title'] : '';
        if ((!$this->hasPermission('HTMLblock::', $title . '::', ACCESS_OVERVIEW))
        || (!$this->hasPermission('HTMLblock::bid', '::' . $properties['bid'], ACCESS_OVERVIEW))) {
            return '';
        }

        return $this->renderView('@ZikulaBlocksModule/Block/htmlblock.html.twig', [
            'content' => $properties['content']
        ]);
    }

    public function getFormClassName(): string
    {
        return HtmlBlockType::class;
    }

    public function getFormTemplate(): string
    {
        return '@ZikulaBlocksModule/Block/html_modify.html.twig';
    }
}
