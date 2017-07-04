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
     * Display hook for view/display templates
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_DISPLAY_VIEW = 'display_view';

    /**
     * Display hook for create/edit forms
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_FORM_EDIT = 'form_edit';

    /**
     * Display hook for delete dialogues
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_FORM_DELETE = 'form_delete';

    /**
     * Used to validate input from a create/edit form
     * dispatches \Zikula\Bundle\HookBundle\Hook\ValidationHook
     */
    const TYPE_VALIDATE_EDIT = 'validate_edit';

    /**
     * Used to validate input from a delete form
     * dispatches \Zikula\Bundle\HookBundle\Hook\ValidationHook
     */
    const TYPE_VALIDATE_DELETE = 'validate_delete';

    /**
     * Perform the final update actions for a create/edit form
     * dispatches \Zikula\Bundle\HookBundle\Hook\ProcessHook
     */
    const TYPE_PROCESS_EDIT = 'process_edit';

    /**
     * Perform the final delete actions for a delete form
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
