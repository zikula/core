<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Example hook handlers.
 *
 * This is a group of handlers that are designed to work together (by maintaining
 * some form of persistence).  For example, view, create, edit and delete a
 * given type of item.
 *
 * This file contains a mix of real and pseudocode to 'give you the gist' of
 * how this should be implemented.  It's not intended to be a copy and paste
 * example.
 */
class Example_HookHandler extends Zikula_HookHandler
{

    /**
     * Display hook for view.
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] Is the id of the object.
     * args[module] the module who notified of this event.
     *
     * @param Zikula_Event $event The hookable event.
     *
     * @return void
     */
    function ui_view(Zikula_Event $event)
    {
        // security check - return void if not allowed.

        $module = $event['caller'];
        $id = $event['id'];

        // view - get from data"base - if not found, render error template or issue a logutil
        $comment = get_comment_from_db("where id = $id AND module = $module"); // fake database call

        $view = Zikula_View::getInstance('Comments');
        $view->assign('comment', $comment);

        // add this response to the event stack
        $name = 'hookhandler.comment.general.ui.view';
        $event->data[$name] = new Zikula_Response_DisplayHook($name, $view, 'areaname_ui_view.tpl');
    }

    /**
     * Display hook for edit views.
     *
     * Subject is the object being created/edited that we're attaching to.
     * args[id] Is the ID of the subject.
     * args[module] the module who notified of this event.
     *
     * @param Zikula_Event $event The hookable event.
     *
     * @return void
     */
    function ui_edit(Zikula_Event $event)
    {
        // security check - return void if not allowed.

        $module = $event['caller'];
        $id = $event['id'];

        if (!$this->validation) {
            // since no validation object exists, this is the first time display of the create/edit form.
            // either display an empty form, for a create action, or query the database for a exiting object.
            if (!$id) {
                // this is a create action so create a new empty object for editing
                $comments = array('id' => null, 'commenttext' => '');
            } else {
                // this is an edit action so we need to get the data from the DB for editing
                $comments = get_comment_from_db("where id = $id AND module = $module"); // fake database call
            }
        } else {
            // this is a re-entry because the form didn't validate.
            // We need to gather the input from the form and render display
            // get the input from the form (this was populated by the validation hook).
            $comments = $this->validation->getObject();
        }

        // create a view and assign data for display
        $view = Zikula_View::getInstance('Comments');
        $view->assign('hook_comments', $comments);

        // add this response to the event stack
        $name = "hookhandler.commants.general.ui.edit";
        $event->data[$name] = new Zikula_Response_DisplayHook($name, $view, "areaname_ui_edit.tpl");
    }

    /**
     * Example validation handler for validate.* hook type.
     *
     * The property $event->data is an instance of Zikula_Collection_HookValidationProviders
     * Use the $event->data->set() method to log the validation response.
     *
     * This method populates this hookhandler object with a Zikula_Provider_HookValidation
     * so the information is available to the ui_edit method if validation fails,
     * and so the process_* can write the validated data to the database.
     *
     * This handler works for create and edit actions equally.
     *
     * @param Zikula_Event $event The hookable event.
     *
     * @return void
     */
    function validate_edit(Zikula_Event $event)
    {
        // validation checks
        $comments = FormUtil::getPassedValue('hook_comments', null, 'POST');
        $this->validation = new Zikula_Provider_HookValidation('comments', $comments);
        if (strlen($comments['name'] < 2)) {
            $this->validation->addError('name', 'Name must be at least 3 characters long.');
        }

        $this->data->set('hookhandler.comments.ui.edit', $this->validation);
    }

    /**
     * Example process update hook handler.
     *
     * This should be executed only if the validation has succeeded.
     * This is used for both new and edit actions.  We can determine which
     * by the presence of an ID field or not.
     *
     * Subject is the object being created/edited that we're attaching to.
     * args[id] Is the ID of the subject.
     * args[module] the module who notified of this event.
     *
     * @param Zikula_Event $event The hookable event.
     *
     * @return void
     */
    function process_update(Zikula_Event $event)
    {
        if (!$this->validation) {
            return;
        }

        $object = $this->validation->getObject();
        if (!$event['id']) {
            // new so do an INSERT
        } else {
            // existing so do an UPDATE
        }
    }

    /**
     * Example delete process hook handler.
     *
     * The subject should be the object that was deleted.
     * args[id] Is the is of the object
     * args[module] is the name of who notified this event.
     *
     * @param Zikula_Event $event The hookable event.
     *
     * @return void
     */
    function process_delete(Zikula_Event $event)
    {
        delete("where id = $event[id] AND module = $event[caller]");
    }

}
