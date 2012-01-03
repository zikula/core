<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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
 * This is the User controller class providing navigation and interaction functionality.
 */
class ExampleDoctrine_Controller_User extends Zikula_AbstractController
{
    /**
     * This method is the default function.
     *
     * Called whenever the module's Admin area is called without defining arguments.
     *
     * @param array $args Array.
     *
     * @return string|boolean Output.
     */
    public function main($args)
    {
        return $this->view();
    }

    /**
     * This method provides a generic item list overview.
     *
     * @return string|boolean Output.
     */
    public function view()
    {
        if (!SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_READ)) {
            return LogUtil::registerPermissionError(ModUtil::url('ExampleDoctrine', 'user', 'main'));
        }

        $em = $this->getService('doctrine.entitymanager');
        $users = $em->getRepository('ExampleDoctrine_Entity_User')->findAll();

        return $this->view->assign('users', $users)
                          ->fetch('exampledoctrine_user_view.tpl');
    }

    /**
     * Create or edit record.
     *
     * @return string|boolean Output.
     */
    public function edit() {
//        if (!SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_ADD)) {
//           return LogUtil::registerPermissionError(ModUtil::url('ExampleDoctrine', 'user', 'main'));
//        }

        $id = $this->request->query->getInt('id');
        if ($id) {
            // load user with id
            $user = $this->entityManager->find('ExampleDoctrine_Entity_User', $id);

            if (!$user) {
                return LogUtil::registerError($this->__f('User with id %s not found', $id));
            }
        } else {
            $user = new ExampleDoctrine_Entity_User();
        }

        /* @var $form Symfony\Component\Form\Form */
        $form = $this->serviceManager->getService('symfony.formfactory')
                     ->create(new ExampleDoctrine_Form_UserType(), $user);
        
        if($this->request->getMethod() == 'POST') {
            $form->bindRequest($this->request);
            
            if($form->isValid()) {
                $data = $form->getData();
                $this->entityManager->persist($data);
                $this->entityManager->flush();
                return $this->redirect(ModUtil::url('ExampleDoctrine', 'user','view'));
            }
        }

        return $this->view->assign('form', $form->createView())
                          ->fetch('exampledoctrine_user_form.tpl');
    }
}