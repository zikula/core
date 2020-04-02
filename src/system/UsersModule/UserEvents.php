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
     * A hook-like UI event that is triggered when a user's account detail is viewed. This allows another module
     * to intercept the display of the user account detail in order to add its own information.
     * To add display elements to the user account detail, render output and add this as an element in the event's
     * data array.
     * The subject contains the user's account record.
     * The `'id'` argument contain's the user's uid.
     */
    public const DISPLAY_VIEW = 'module.users.ui.display_view';

    /**
     * A hook-like event process that is triggered when the delete confirmation form is displayed. It allows other modules
     * to intercept and add to the delete confirmation form.
     * The subject of the event is not set.
     * The the argument `'id'` is the uid of the user who will be deleted if confirmed.
     */
    public const DELETE_FORM = 'module.users.ui.form_delete';

    public const DELETE_VALIDATE = 'module.users.ui.validate_delete';

    public const DELETE_PROCESS = 'module.users.ui.process_delete';

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
