<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle;


interface HookInterface
{
    /**
     * The bundleName
     * e.g. return 'ZikulaFooHookModule';
     * @return string
     */
    public function getOwner();

    /**
     * The Category type
     * e.g. return \Zikula\Bundle\HookBundle\Category\FormAwareCategory::NAME;
     * @return string
     */
    public function getCategory();

    /**
     * Translated string to display as a title in the hook UI
     * e.g. return $translator->__('FooHook FormAware Provider');
     * @return string
     */
    public function getTitle();
}
