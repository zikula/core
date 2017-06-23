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

class UiHooksCategory implements CategoryInterface
{
    const NAME = 'ui_hooks';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_DISPLAY_VIEW = 'display_view';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_FORM_EDIT = 'form_edit';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_FORM_DELETE = 'form_delete';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\ValidationHook
     */
    const TYPE_VALIDATE_EDIT = 'validate_edit';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\ValidationHook
     */
    const TYPE_VALIDATE_DELETE = 'validate_delete';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\ProcessHook
     */
    const TYPE_PROCESS_EDIT = 'process_edit';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\ProcessHook
     */
    const TYPE_PROCESS_DELETE = 'process_delete';

    public function getName()
    {
        return self::NAME;
    }

    public function getTypes()
    {
        return [
            self::TYPE_DISPLAY_VIEW,
            self::TYPE_FORM_EDIT,
            self::TYPE_VALIDATE_EDIT,
            self::TYPE_PROCESS_EDIT,
            self::TYPE_FORM_DELETE,
            self::TYPE_VALIDATE_DELETE,
            self::TYPE_PROCESS_DELETE,
        ];
    }
}
