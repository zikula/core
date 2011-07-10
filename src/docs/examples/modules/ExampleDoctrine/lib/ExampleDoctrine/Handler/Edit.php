<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaExamples_ExampleDoctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Form handler for create and edit.
 */
class ExampleDoctrine_Handler_Edit extends Zikula_Form_AbstractHandler
{
    /**
     * User id.
     *
     * When set this handler is in edit mode.
     *
     * @var integer
     */
    private $_id;
    
    private $_user;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     */
    public function initialize(Zikula_Form_View $view)
    {
        // load and assign registred categories
        $registryCategories  = CategoryRegistryUtil::getRegisteredModuleCategories('ExampleDoctrine', 'exampledoctrine_users', 'id');
        $categories = array();
        foreach ($registryCategories as $property => $cid) {
            $categories[(int)$property] = (int)$cid;
        }

        $view->assign('registries', $categories);

        $id = FormUtil::getPassedValue('id', null, "GET", FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            // load user with id
            $user = $this->entityManager->find('ExampleDoctrine_Entity_User', $id);

            if ($user) {
                // switch to edit mode
                $this->_id = $id;
            } else {
                return LogUtil::registerError($this->__f('User with id %s not found', $id));
            }
        } else {
            $user = new ExampleDoctrine_Entity_User();
        }

        // assign current values to form fields
        $view->assign('user', $user);
        $view->assign($user->toArray());
        $this->_user = $user;
        
        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Args.
     *
     * @return boolean
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        // load form values
        $data = $view->getValues();
        
        $user = $this->_user;
        $user->merge($data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $view->redirect(ModUtil::url('ExampleDoctrine', 'user','view'));
    }
}

