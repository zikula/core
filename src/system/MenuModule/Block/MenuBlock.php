<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\MenuModule\Block\Form\Type\MenuType;

class MenuBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered block
     */
    public function display(array $properties)
    {
        $properties['options'] = json_decode($properties['options'], true);

        return $this->renderView('@ZikulaMenuModule/Block/menu.html.twig', $properties);
    }

    public function getFormClassName()
    {
        return MenuType::class;
    }
}
