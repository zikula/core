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
 * Example controller workflow.
 *
 * This file contains a mix of real and pseudocode to 'give you the gist' of
 * how this should be implemented.  It's not intended to be a copy and paste
 * example.
 */
class Example_Controller_User extends Zikula_AbstractController
{
    /**
     * View action.
     *
     * @return string Action output.
     */
    public function view()
    {
        $id = FormUtil::getPassedValue('id', null, 'GET');
        $this->throwNotFoundIf(!$id);

        $article = dbcall('...');
        $this->view->assign('artcile', $article);
        $this->view->fetch('example_user_view.tpl');

        // note the called template should execute
        // {notifydisplayhooks eventname='example.ui_hooks.general.display_view' id=$article[id]}
    }

    /**
     * Edit action.
     *
     * @return string Edit form output.
     */
    public function edit()
    {
        $article = FormUtil::getPassedValue('article', array(), 'POST');
        $id = FormUtil::getPassedValue('id', null, 'GET');
        $submit = FormUtil::getPassedValue('submit', null, 'GET');

        if (!$id && !$submit) {
            // create action
            $article = array('title' => $title, 'body' => $body);
            // or if using Doctrine/DBObject something like this:
            $article = new Example_Model_Article();
            $article->setTitle($title);
            $article->setBody($body);
            $article->save();
        } elseif ($id && !$submit) {
            // starting edit action (display item to be edited).
            $article = dbcall("where id = $id...");
        }

        // handle submit (validate and commit as appropriate).
        if ($submit) {
            // Do our validations
            $articleValid = $this->validateArticle($article); // a method of this class which validates articles.

            // validate any hooks
            $validators = new Zikula_Collection_HookValidationProviders();
            $hook = new Zikula_ValidationHook('example.ui_hooks.general.validate_edit', $article['id'], $this, $validators);
            $this->notifyHooks($hook);
            if (!$validators->hasErrors() && !$articleValid) {
                // commit to the database
                $article->save();

                $url = new Zikula_ModUrl('Example', 'user', 'edit', 'en', array('id' => $id));
                // notify any hooks they may now commit the as the original form has been committed.
                $hook = new Zikula_ProcessHook('example.ui_hooks.general.process_edit', $id, $url);
                $this->notifyHooks($hook);
            }
        }

        $this->view->assign('article', $article);
        $this->view->fetch('example_user_edit.tpl');

        // note the called template should execute
        // {notifydisplayhooks eventname='example.ui_hooks.general.form_edit' subject=$article id=$article[id]}
    }

    /**
     * Delete action.
     *
     * @return void
     */
    public function delete()
    {
        $id = FormUtil::getPassedValue('id', null, 'GET');
        $submit = FormUtil::getPassedValue('submit', null, 'GET');

        $article = db_get("select where id = $id");
        $article->delete();

        $this->notifyHooks('example.ui_hooks.general.process_delete', $article, $id);
    }

}
