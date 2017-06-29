Hook Types
----------
The following is a list of valid hook types.

### 'ui_hooks' category

    see \Zikula\Bundle\HookBundle\Category\UiHooksCategory

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
