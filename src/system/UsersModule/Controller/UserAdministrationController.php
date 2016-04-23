<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\UserEvents;

/**
 * Class UserAdministrationController
 * @Route("/admin")
 */
class UserAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{sort}/{sortdir}/{letter}/{startnum}")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @param string $sort
     * @param string $sortdir
     * @param string $letter
     * @param integer $startnum
     * @return array
     */
    public function listAction(Request $request, $sort = 'uid', $sortdir = 'DESC', $letter = null, $startnum = 0)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulausersmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid'), new Column('user_regdate'), new Column('lastlogin'), new Column('activated')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'startnum' => $startnum
        ]);

        $filter = [];
        $filter['activated'] = ['operator' => 'notIn', 'operand' => [
            UsersConstant::ACTIVATED_PENDING_REG,
            UsersConstant::ACTIVATED_PENDING_DELETE
        ]];
        if (!empty($letter)) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "$letter%"];
        }
        $limit = $this->getVar(UsersConstant::MODVAR_ITEMS_PER_PAGE);
        $users = $this->get('zikula_users_module.user_repository')->query(
            $filter,
            [$sort => $sortdir],
            $limit,
            $startnum
        );

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => [
                'count' => $users->count(),
                'limit' => $limit
            ],
            'users' => $users
        ];
    }

    /**
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     *
     * @Route("/getusersbyfragmentastable", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return PlainResponse
     */
    public function getUsersByFragmentAsTableAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'activated' => ['operator' => 'notIn', 'operand' => [
                UsersConstant::ACTIVATED_PENDING_REG,
                UsersConstant::ACTIVATED_PENDING_DELETE
            ]],
            'uname' => ['operator' => 'like', 'operand' => "$fragment%"]
        ];
        $users = $this->get('zikula_users_module.user_repository')->query($filter);

        return $this->render('@ZikulaUsersModule/UserAdministration/userlist.html.twig', ['users' => $users], new PlainResponse());
    }

    /**
     * @Route("/user/create")
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $user = new UserEntity();
        $form = $this->createForm('Zikula\UsersModule\Form\Type\AdminCreatedUserType',
            $user, ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_VALIDATE_NEW, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(UserEvents::HOOK_USER_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $user = $form->getData();
                $registrationErrors = $this->get('zikulausersmodule.helper.registration_helper')->registerNewUser(
                    $user,
                    false,
                    $form['usernotification']->getData(),
                    $form['adminnotification']->getData(),
                    $form['sendpass']->getData()
                );
                if (empty($registrationErrors)) {
                    $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
                    $this->get('event_dispatcher')->dispatch(UserEvents::USER_PROCESS_NEW, $event);
                    $hook = new ProcessHook($user->getUid());
                    $this->get('hook_dispatcher')->dispatch(UserEvents::HOOK_USER_PROCESS, $hook);

                    if ($user->getActivated() == UsersConstant::ACTIVATED_PENDING_REG) {
                        $this->addFlash('status', $this->__('Done! Created new registration application.'));
                    } elseif (null !== $user->getActivated()) {
                        $this->addFlash('status', $this->__('Done! Created new user account.'));
                    } else {
                        $this->addFlash('error', $this->__('Warning! New user information has been saved, however there may have been an issue saving it properly.'));
                    }

                    return $this->redirectToRoute('zikulausersmodule_admin_view');
                } else {
                    $this->addFlash('error', $this->__('Errors creating user!'));
                    foreach ($registrationErrors as $registrationError) {
                        $this->addFlash('error', $registrationError);
                    }
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/modify/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function modifyAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', $user->getUname() . "::" . $user->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (1 === $user->getUid()) {
            throw new AccessDeniedException($this->__("Error! You can't edit the guest account."));
        }

        $form = $this->createForm('Zikula\UsersModule\Form\Type\AdminModifyUserType',
            $user, ['translator' => $this->get('translator.default')]
        );
        $originalUser = clone $user;
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_VALIDATE_MODIFY, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(UserEvents::HOOK_USER_VALIDATE, $hook);
        $validators = $hook->getValidators();

        /**
         * @todo CAH 22 Apr 2016
         * In previous version, user was not allowed to edit certain properties if editing himself:
         *  - group membership in 'certain system groups'
         *     - the 'default' group
         *     - primary admin group
         *  - activated state
         * The fields were disabled in the form.
         * User was not able to delete self. (button removed from form)
         *
         * It is possible users will not have a password if their account is provided externally. If they do not,
         *  this may need to change the text displayed to users, e.g. 'change' -> 'create', etc.
         *  Setting the password may 'disable' the external authorization and the editor should be made aware.
         */

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $user = $form->getData();
                // @todo hash new password if set @see UserUtil::setPassword
                $this->get('doctrine')->getManager()->flush($user);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalUser->getUname()];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $this->get('event_dispatcher')->dispatch(UserEvents::UPDATE_ACCOUNT, $updateEvent);

                $this->get('event_dispatcher')->dispatch(UserEvents::USER_PROCESS_MODIFY, new GenericEvent($user));
                $this->get('hook_dispatcher')->dispatch(UserEvents::HOOK_USER_PROCESS, new ProcessHook($user->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));

            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
