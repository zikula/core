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

class FormAwareCategory implements CategoryInterface
{
    const NAME = 'form_aware_hook';

    /**
     * Display hook for display templates
     * dispatches \Zikula\Bundle\HookBundle\Hook\DisplayHook
     */
    const TYPE_DISPLAY = 'display';

    /**
     * Display hook for create/edit forms
     * dispatches \Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook
     */
    const TYPE_EDIT = 'edit';

    /**
     * Display hook for delete forms
     * dispatches \Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook
     */
    const TYPE_DELETE = 'delete';

    /**
     * Process the results of the edit form after the main form is processed
     * dispatches \Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse
     */
    const TYPE_PROCESS_EDIT = 'process_edit';

    /**
     * Process the results of the delete form after the main form is processed
     * dispatches \Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse
     */
    const TYPE_PROCESS_DELETE = 'process_delete';

    public function getName()
    {
        return self::NAME;
    }

    public function getTypes()
    {
        return [
            self::TYPE_DISPLAY,
            self::TYPE_EDIT,
            self::TYPE_DELETE,
            self::TYPE_PROCESS_EDIT,
            self::TYPE_PROCESS_DELETE,
        ];
    }
}
