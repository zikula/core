<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Category;


interface CategoryInterface
{
    /**
     * The name of a hook category defines a contract of types which both subscriber and provider implement
     * @return string
     */
    public function getName();

    /**
     * Hook category types are events that are contracted to be called by the provider.
     * @return array
     */
    public function getTypes();
}
