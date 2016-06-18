<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule;

/**
 * Class UserEvents
 * Contains constant values for user event names, including hook events.
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
    const USER_DISPLAY_VIEW = 'module.users.ui.display_view';

    const USER_VALIDATE_NEW = 'module.users.ui.validate_edit.new_user';

    const USER_VALIDATE_MODIFY = 'module.users.ui.validate_edit.modify_user';

    /**
     * A hook-like event that is triggered when the delete confirmation form is submitted and the submitted data
     * is being validated prior to processing. It allows other modules to intercept and add to the delete confirmation
     * form, and in this case to validate the data entered on the portion of the delete confirmation form that
     * they injected with the corresponding `form_delete` event.
     * The subject of the event is not set.
     * The the argument `'id'` is the uid of the user who will be deleted if confirmed.
     */
    const USER_VALIDATE_DELETE = 'module.users.ui.validate_delete';

    const USER_PROCESS_NEW = 'module.users.ui.process_edit.new_user';

    const USER_PROCESS_MODIFY = 'module.users.ui.process_edit.modify_user';

    /**
     * A hook-like event that is triggered when the delete confirmation form is submitted and the submitted data
     * is has validated. It allows other modules to intercept and add to the delete confirmation
     * form, and in this case to process the data entered on the portion of the delete confirmation form that
     * they injected with the corresponding `form_delete` event. This event will be triggered after the
     * `user.account.delete` event.
     * The subject of the event is not set.
     * The the argument `'id'` is the uid of the user who will be deleted if confirmed.
     */
    const USER_PROCESS_DELETE = 'module.users.ui.process_delete';

    /**
     * Occurs after a user account is created. All handlers are notified. It does not apply to creation of a pending
     * registration. The full user record created is available as the subject. This is a storage-level event,
     * not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the user record that was created.
     */
    const CREATE_ACCOUNT = 'user.account.create';

    /**
     * Occurs after a user is updated. All handlers are notified. The full updated user record is available
     * as the subject. This is a storage-level event, not a UI event. It should not be used for UI-level
     * actions such as redirects.
     * The subject of the event is set to the user record, with the updated values.
     */
    const UPDATE_ACCOUNT = 'user.account.update';

    /**
     * Occurs after the deletion of a user account. Subject is $uid.
     */
    const DELETE_ACCOUNT = 'user.account.delete';

    /**
     * A hook-like event triggered when the adminitstrator's new user form is displayed, which allows other
     * modules to intercept and display their own elements for submission on the new user form.
     * To add elements to the new user form, render output and add this as an array element on the event's
     * data array.
     * There is no subject and no arguments for the event.
     */
    const FORM_NEW = 'module.users.ui.form_edit.new_user';

    /**
     * A hook-like event triggered when the modify user form is displayed, which allows other
     * modules to intercept and display their own elements for submission on the new user form.
     * To add elements to the modify user form, render output and add this as an array element on the event's
     * data array.
     * The subject contains the current state of the user object, possibly edited from its original state.
     * The `'id'` argument contains the uid of the user account.
     */
    const FORM_MODIFY = 'module.users.ui.form_edit.modify_user';

    /**
     * A hook-like event that is triggered when the delete confirmation form is displayed. It allows other modules
     * to intercept and add to the delete confirmation form.
     * The subject of the event is not set.
     * The the argument `'id'` is the uid of the user who will be deleted if confirmed.
     */
    const FORM_DELETE = 'module.users.ui.form_delete';

    /**
     * A hook-like UI event triggered when the users search form is displayed. Allows other
     * modules to intercept and insert their own elements for submission to the search form.
     * To add elements to the search form, render the output and then add this as an array element to the event's
     * data array.
     * This event does not have a subject or arguments.
     */
    const FORM_SEARCH = 'module.users.ui.form_edit.search';

    /**
     * currently no longer used...
     */
    const FORM_SEARCH_PROCESS = 'users.search.process_edit';

    /**
     * Occurs after the Users module configuration has been updated via the administration interface.
     * Event data is populated by the new values.
     */
    const CONFIG_UPDATED = 'module.users.config.updated';

    /**
     * Occurs right after a successful logout. All handlers are notified.
     * The event's subject contains the user's UserEntity
     * Args contain array of `['authentication_method' => $authenticationMethod,
     * 'uid'=> $uid];`
     */
    const USER_LOGOUT_SUCCESS = 'module.users.ui.logout.succeeded';
}
