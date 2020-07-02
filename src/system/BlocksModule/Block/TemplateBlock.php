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
use Zikula\BlocksModule\Block\Form\Type\TemplateBlockType;

class TemplateBlock extends AbstractBlockHandler
{
    public function display(array $properties): string
    {
        $title = !empty($properties['title']) ? $properties['title'] : '';
        if ((!$this->hasPermission('Templateblock::', $title . '::', ACCESS_OVERVIEW))
        || (!$this->hasPermission('Templateblock::bid', '::' . $properties['bid'], ACCESS_OVERVIEW))) {
            return '';
        }
        $path = $properties['path'] ?? '';
        if (empty($path) || !$this->twig->getLoader()->exists($path)) {
            return '';
        }

        return $this->renderView($path);
    }

    public function getFormClassName(): string
    {
        return TemplateBlockType::class;
    }
}
