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

namespace Zikula\MenuModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\MenuModule\Block\Form\Type\MenuType;

class MenuBlock extends AbstractBlockHandler
{
    public function display(array $properties): string
    {
        $properties['options'] = json_decode($properties['options'], true);

        return $this->renderView('@ZikulaMenuModule/Block/menu.html.twig', $properties);
    }

    public function getFormClassName(): string
    {
        return MenuType::class;
    }
}
