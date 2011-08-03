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
        $categories  = CategoryRegistryUtil::getRegisteredModuleCategories('ExampleDoctrine', 'User', 'id');
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

        $userData = $user->toArray();
        
        // overwrite attributes array entry with a form compitable format
        $field1 = $user->getAttributes()->get('field1')? $user->getAttributes()->get('field1')->getValue() : '';
        $field2 = $user->getAttributes()->get('field2')? $user->getAttributes()->get('field2')->getValue() : '';
        $userData['attributes'] = array('field1' => $field1,
                                        'field2' => $field2);
        
        // assign current values to form fields
        $view->assign('user', $user)
             ->assign('meta', $user->getMetadata() != null? $user->getMetadata()->toArray() : array())
             ->assign($userData);
        
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
        
        // merge attributes
        foreach($data['attributes'] as $name => $value) {
            $user->setAttribute($name, $value);
        }
        
        // merge metadata
        $metadata = $user->getMetadata();
        
        if($metadata == null) {
            $metadata = new ExampleDoctrine_Entity_UserMetadata($user);
            $user->setMetadata($metadata);
        }
        
        $metadata->merge($data['meta']);
        
        unset($data['attributes'], $data['meta']);
        
        // merge user and save everything
        $user->merge($data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $view->redirect(ModUtil::url('ExampleDoctrine', 'user','view'));
    }
}

