<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaExamples_Hooks
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
 * The reason we are using an instance of Zikula_HookHandler is to maintain
 * some kind of persistence through the ui, validate and process
 * actions in the workflow to avoid double validation for example, once in the
 * controller and again in the template.  All the ui, validate, and process handlers
 * worktogether and would be part of the SAME bundle, and have the SAME serviceId.
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
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_DisplayHook $hook The hookable event.
     *
     * @return void
     */
    public function ui_view(Zikula_DisplayHook $hook)
    {
        // security check - return void if not allowed.

        $module = $hook->getCaller();
        $id = $hook->getId();

        // view - get from data"base - if not found, render error template or issue a logutil
        $comment = get_comment_from_db("where id = $id AND module = $module"); // fake database call

        $view = Zikula_View::getInstance('Comments');
        $view->assign('comment', $comment);

        // add this response to the event stack
        // the area names are the names of *THIS* provider's area
        $response = new Zikula_Response_DisplayHook('modulehook_area.modname.area', $view, 'areaname_ui_view.tpl');
        $hook->setResponse($response);
    }

    /**
     * Display hook for edit views.
     *
     * Subject is the object being created/edited that we're attaching to.
     * args[id] Is the ID of the subject.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_DisplayHook $hook The hookable event.
     *
     * @return void
     */
    public function ui_edit(Zikula_DisplayHook $hook)
    {
        // security check - return void if not allowed.

        $module = $hook->getCaller();
        $id = $hook->getId();


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
        $response = new Zikula_Response_DisplayHook($name, $view, "areaname_ui_edit.tpl");
        $hook->setResponse($response);
    }

    /**
     * Example validation handler for validate.* hook type.
     *
     * The property $hook->data is an instance of Zikula_Collection_HookValidationProviders
     * Use the $hook->data->set() method to log the validation response.
     *
     * This method populates this hookhandler object with a Zikula_Hook_ValidationResponse
     * so the information is available to the ui_edit method if validation fails,
     * and so the process_* can write the validated data to the database.
     *
     * This handler works for create and edit actions equally.
     *
     * @param Zikula_ValidationHook $hook The hookable event.
     *
     * @return void
     */
    public function validate_edit(Zikula_ValidationHook $hook)
    {
        // validation checks
        $comments = FormUtil::getPassedValue('hook_comments', null, 'POST');
        $this->validation = new Zikula_Hook_ValidationResponse('comments', $comments);
        if (strlen($comments['name'] < 2)) {
            $this->validation->addError('name', 'Name must be at least 3 characters long.');
        }

        $hook->setValidator('hookhandler.comments.form_edit', $this->validation);
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
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_ProcessHook $hook The hookable event.
     *
     * @return void
     */
    public function process_update(Zikula_ProcessHook $hook)
    {
        if (!$this->validation) {
            return;
        }

        $object = $this->validation->getObject();
        if (!$hook->getId()) {
            $urlData = $hook->getUrl()->serialize();
            $areaId = $hook->getAreaId();
            $caller = $hook->getCaller();
            // new so do an INSERT including the $urlData, and $hook->getAreaId();
        } else {
            // existing so do an UPDATE
        }
    }

    /**
     * Example delete process hook handler.
     *
     * The subject should be the object that was deleted.
     * args[id] Is the is of the object
     * args[caller] is the name of who notified this event.
     *
     * @param Zikula_ProcessHook $hook The hookable event.
     *
     * @return void
     */
    public function process_delete(Zikula_ProcessHook $hook)
    {
        delete("where id = {$hook->getId()} AND module = {$hook->getCaller()}");
    }

    /**
     * Filter hook (OPTIONAL) - READ BELOW.
     *
     * This would not normally be grouped in the same area as the the
     * other ui, process and validate hook handlers.  Logically this handler
     * DOES NOT belong grouped with the other handlers because it's not part of
     * the workflow of a display hook that requires validation and processing.
     * Normally these kind of handlers would be in their own area, and there
     * may even be multiple filters, each in a different area.
     *
     * The filter receives the Zikula_View as the subject
     * (from the template that invoked it).
     *
     * Subject is the Zikula_View.
     * $hook->data is the data to be filtered (or not).
     *
     * There is nothing to return.  If the filter decides to
     * run then it should just alter the $hook->data property of the
     * event.
     *
     * @param Zikula_FilterHook $hook The hookable event.
     *
     * @return void
     */
    public function filter(Zikula_FilterHook $hook)
    {
        if (somecontition) {
            return;
        }

        // do the actual filtering (or not)
        $hook->data = str_replace('FOO', 'BAR', $this->data);
    }

}
