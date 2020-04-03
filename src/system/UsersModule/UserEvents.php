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

namespace Zikula\UsersModule;

/**
 * Class UserEvents
 */
class UserEvents
{
    /**
     * A hook-like UI event triggered when the users search form is displayed. Allows other
     * modules to intercept and insert their own elements for submission to the search form.
     * To add elements to the search form, render the output and then add this as an array element to the event's
     * data array.
     * This event does not have a subject or arguments.
     */
    public const FORM_SEARCH = 'module.users.ui.form_edit.search';

    /**
     * currently no longer used...
     */
    public const FORM_SEARCH_PROCESS = 'users.search.process_edit';

    /**
     * Occurs after the Users module configuration has been updated via the administration interface.
     * Event data is populated by the new values.
     */
    public const CONFIG_UPDATED = 'module.users.config.updated';
}
