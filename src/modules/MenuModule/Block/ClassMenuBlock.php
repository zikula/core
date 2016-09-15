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

/**
 * Block to display drop-down menu in bootstrap nav bar from a class
 */
class ClassMenuBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered block
     */
    public function display(array $properties)
    {
        return $this->renderView('@ZikulaMenuModule/Block/classMenuBlock.html.twig', [
            'className' => 'ZikulaMenuModule:AdminMenu:adminMenu'
        ]);
    }

//    public function getFormClassName()
//    {
//        return 'Zikula\BlocksModule\Block\Form\Type\TextBlockType';
//    }
}
