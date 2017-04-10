<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api\ApiInterface;

use Zikula\Core\AbstractModule;
use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockFactoryApiInterface
 */
interface BlockFactoryApiInterface
{
    /**
     * Factory method to create an instance of a block given its name and the providing module instance.
     *  Supports either Zikula\BlocksModule\BlockHandlerInterface or
     *  Zikula_Controller_AbstractBlock (to be removed).
     *
     * @param $blockClassName
     * @param AbstractModule $moduleBundle
     * @return BlockHandlerInterface
     */
    public function getInstance($blockClassName, AbstractModule $moduleBundle);
}
