<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Category;

class FormAwareCategory implements CategoryInterface
{
    public const NAME = 'form_aware_hook';

    /**
     * Display hook for create/edit forms.
     * Dispatches FormAwareHook instances.
     */
    public const TYPE_EDIT = 'edit';

    /**
     * Display hook for delete forms.
     * Dispatches FormAwareHook instances.
     */
    public const TYPE_DELETE = 'delete';

    /**
     * Process the results of the edit form after the main form is processed.
     * Dispatches FormAwareHook instances.
     */
    public const TYPE_PROCESS_EDIT = 'process_edit';

    /**
     * Process the results of the delete form after the main form is processed.
     * Dispatches FormAwareResponse instances.
     */
    public const TYPE_PROCESS_DELETE = 'process_delete';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTypes(): array
    {
        return [
            self::TYPE_EDIT,
            self::TYPE_DELETE,
            self::TYPE_PROCESS_EDIT,
            self::TYPE_PROCESS_DELETE
        ];
    }
}
