<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ExampleModule\Controller;

use LogUtil, SecurityUtil, ModUtil;
use ExampleModule\Entity\User;
use ExampleModule\Form\UserType;

/**
 * This is the User controller class providing navigation and interaction functionality.
 */
class UserController extends \Zikula\Framework\Controller\AbstractController
{
    /**
     * This method is the default function.
     * Called whenever the module's Admin area is called without defining arguments.
     *
     * @param array $args Array.
     *
     * @return string|boolean Output.
     */
    public function mainAction()
    {
        return $this->view();
    }

    /**
     * This method provides a generic item list overview.
     *
     * @return string|boolean Output.
     */
    public function viewAction()
    {
        if (!SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_READ)) {
            return LogUtil::registerPermissionError(ModUtil::url('ExampleDoctrine', 'user', 'index'));
        }

        $em = $this->get('doctrine.entitymanager');
        $users = $em->getRepository('ExampleDoctrine_Entity_User')->findAll();

        return $this->view->assign('users', $users)
            ->fetch('exampledoctrine_user_view.tpl');
    }

    /**
     * Create or edit record.
     *
     * @return string|boolean Output.
     */
    public function editAction()
    {
        if (!SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(ModUtil::url('ExampleDoctrine', 'user', 'index'));
        }

        $id = $this->request->query->getInt('id');
        if ($id) {
            // load user with id
            $user = $this->entityManager->find('ExampleDoctrine_Entity_User', $id);

            if (!$user) {
                return LogUtil::registerError($this->__f('User with id %s not found', $id));
            }
        } else {
            $user = new User();
        }

        /* @var $form \Symfony\Component\Form\Form */
        $form = $this->container->get('symfony.formfactory')
            ->create(new UserType(), $user);

        if ($this->request->getMethod() == 'POST') {
            $form->bindRequest($this->request);

            if ($form->isValid()) {
                $data = $form->getData();
                $this->entityManager->persist($data);
                $this->entityManager->flush();
                return $this->redirect(ModUtil::url('ExampleDoctrine', 'user', 'view'));
            }
        }

        return $this->view->assign('form', $form->createView())
            ->fetch('exampledoctrine_user_edit.tpl');
    }
}
