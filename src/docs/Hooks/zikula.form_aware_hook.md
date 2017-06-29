Hook Types
----------
The following is a list of valid hook types.

### 'form_aware_hook' category

    see \Zikula\Bundle\HookBundle\Category\FormAwareCategory

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
     * dispatches \Zikula\UsersModule\Event\FormAwareResponse
     */
    const TYPE_PROCESS_EDIT = 'process_edit';

    /**
     * Process the results of the delete form after the main form is processed
     * dispatches \Zikula\UsersModule\Event\FormAwareResponse
     */
    const TYPE_PROCESS_DELETE = 'process_delete';
