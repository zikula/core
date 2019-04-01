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

namespace Zikula\Bundle\HookBundle;

interface HookInterface
{
    /**
     * The bundleName, e.g. return 'ZikulaFooHookModule';
     */
    public function getOwner(): string;

    /**
     * The Category type, e.g. return \Zikula\Bundle\HookBundle\Category\FormAwareCategory::NAME;
     */
    public function getCategory(): string;

    /**
     * Translated string to display as a title in the hook UI, e.g. return $translator->__('FooHook FormAware Provider');
     */
    public function getTitle(): string;

    /**
     * The area name.
     */
    public function getAreaName(): string;
}
