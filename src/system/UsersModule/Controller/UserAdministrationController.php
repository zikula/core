<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Doctrine\Common\Collections\ArrayCollection;
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
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Container\HookContainer;
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
    public function listAction(Request $request, $sort = 'uid', $sortdir = 'DESC', $letter = 'all', $startnum = 0)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $startnum = $startnum > 0 ? $startnum - 1 : 0;

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulausersmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid'), new Column('user_regdate'), new Column('lastlogin'), new Column('activated')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'startnum' => $startnum
        ]);

        $filter = [];
        if (!empty($letter) && 'all' != $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "$letter%"];
        }
        $limit = $this->getVar(UsersConstant::MODVAR_ITEMS_PER_PAGE, UsersConstant::DEFAULT_ITEMS_PER_PAGE);
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
            'actionsHelper' => $this->get('zikula_users_module.helper.administration_actions'),
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

        return $this->render('@ZikulaUsersModule/UserAdministration/userlist.html.twig', [
            'users' => $users,
            'actionsHelper' => $this->get('zikula_users_module.helper.administration_actions'),
        ], new PlainResponse());
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
        if (!$this->hasPermission('ZikulaZAuthModule::', $user->getUname() . "::" . $user->getUid(), ACCESS_EDIT)) {
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

        $event = new GenericEvent($form->getData(), [], new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(UserEvents::MODIFY_VALIDATE, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(HookContainer::EDIT_VALIDATE, $hook);
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
         */

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $user = $form->getData();
                $this->get('doctrine')->getManager()->flush($user);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalUser->getUname()];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $this->get('event_dispatcher')->dispatch(UserEvents::UPDATE_ACCOUNT, $updateEvent);

                $this->get('event_dispatcher')->dispatch(UserEvents::MODIFY_PROCESS, new GenericEvent($user));
                $this->get('hook_dispatcher')->dispatch(HookContainer::EDIT_PROCESS, new ProcessHook($user->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/approve/{user}/{force}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @param bool $force
     * @return array
     */
    public function approveAction(Request $request, UserEntity $user, $force = false)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $forceVerification = $this->hasPermission('ZikulaUsersModule', '::', ACCESS_ADMIN) && $force;
        $form = $this->createForm('Zikula\UsersModule\Form\RegistrationType\ApproveRegistrationConfirmationType', [
            'user' => $user->getUid(),
            'force' => $forceVerification
        ], [
            'translator' => $this->get('translator.default'),
            'buttonLabel' => $this->__('Approve')
        ]);
        if ($user->isApproved() && !$forceVerification) {
            $this->addFlash('error', $this->__f('Warning! Nothing to do! %sub% is already approved.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_useradministration_list');
        } elseif (!$user->isApproved() && !$forceVerification
            && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $this->addFlash('error', $this->__f('Error! %sub% cannot be approved.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_useradministration_list');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $approved = $this->get('zikula_users_module.helper.registration_helper')->approve($user);
                if ($approved) {
                    $this->addFlash('status', $this->__f('Done! %sub% has been approved.', ['%sub%' => $user->getUname()]));
                } else {
                    $this->addFlash('error', $this->__f('Sorry! There was a problem approving %sub%.', ['%sub%' => $user->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'user' => $user
        ];
    }

    /**
     * @Route("/delete/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @param UserEntity|null $user
     * @return array
     */
    public function deleteAction(Request $request, UserEntity $user = null)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }
        $users = new ArrayCollection();
        if ($request->getMethod() == 'POST') {
            $deleteForm = $this->createForm('Zikula\UsersModule\Form\Type\DeleteType', [], [
                'choices' => $this->get('zikula_users_module.user_repository')->queryBySearchForm(),
                'action' => $this->generateUrl('zikulausersmodule_useradministration_delete'),
                'translator' => $this->get('translator.default')
            ]);
            $deleteForm->handleRequest($request);
            if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
                $data = $deleteForm->getData();
                $users = $data['users'];
            }
        } else {
            if (isset($user)) {
                $users->add($user);
            }
        }
        $uids = [];
        foreach ($users as $user) {
            $uids[] = $user->getUid();
        }
        $usersImploded = implode(',', $uids);

        $deleteConfirmationForm = $this->createForm('Zikula\UsersModule\Form\Type\DeleteConfirmationType', [
            'users' => $usersImploded
        ], [
            'translator' => $this->get('translator.default')
        ]);
        $deleteConfirmationForm->handleRequest($request);
        if (!$deleteConfirmationForm->isSubmitted() && ($users instanceof ArrayCollection) && $users->isEmpty()) {
            throw new \InvalidArgumentException($this->__('No users selected.'));
        }
        if ($deleteConfirmationForm->isSubmitted()) {
            $userIdsImploded = $deleteConfirmationForm->get('users')->getData();
            $userIds = explode(',', $userIdsImploded);
            $valid = true;
            foreach ($userIds as $k => $uid) {
                if (in_array($uid, [1, 2, $this->get('zikula_users_module.current_user')->get('uid')])) {
                    unset($userIds[$k]);
                    $this->addFlash('danger', $this->__f('You are not allowed to delete Uid %uid', ['%uid' => $uid]));
                    continue;
                }
                $event = new GenericEvent(null, ['id' => $uid], new ValidationProviders());
                $validators = $this->get('event_dispatcher')->dispatch(UserEvents::DELETE_VALIDATE, $event)->getData();
                $hook = new ValidationHook($validators);
                $this->get('hook_dispatcher')->dispatch(HookContainer::DELETE_VALIDATE, $hook);
                $validators = $hook->getValidators();
                if ($validators->hasErrors()) {
                    $valid = false;
                }
            }
            if ($valid && $deleteConfirmationForm->isValid()) {
                // @todo add possibilty to 'mark as pending deletion' UsersConstant::ACTIVATED_PENDING_DELETE ???
                // @todo send email to 'denied' registrations. see MailHelper::sendNotification (regdeny)
                $this->get('zikula_users_module.user_repository')->removeArray($userIds);
                $this->addFlash('success', $this->_fn('User deleted!', '%n users deleted!', count($userIds), ['%n' => count($userIds)]));
                foreach ($userIds as $uid) {
                    $this->get('event_dispatcher')->dispatch(UserEvents::DELETE_ACCOUNT, new GenericEvent($uid));
                    $this->get('event_dispatcher')->dispatch(UserEvents::DELETE_PROCESS, new GenericEvent(null, ['id' => $uid]));
                    $this->get('hook_dispatcher')->dispatch(HookContainer::DELETE_PROCESS, new ProcessHook($uid));
                }

                return $this->redirectToRoute('zikulausersmodule_useradministration_list');
            }
        }

        return [
            'users' => $users,
            'form' => $deleteConfirmationForm->createView()
        ];
    }

    /**
     * @Route("/search")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @return array
     */
    public function searchAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm('Zikula\UsersModule\Form\Type\SearchUserType',
            [], ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // @TODO the users.search.process_edit event is no longer dispatched with this method. could it be done in a Transformer?
            $deleteForm = $this->createForm('Zikula\UsersModule\Form\Type\DeleteType', [], [
                'choices' => $this->get('zikula_users_module.user_repository')->queryBySearchForm($form->getData()),
                'action' => $this->generateUrl('zikulausersmodule_useradministration_delete'),
                'translator' => $this->get('translator.default')
            ]);

            return $this->render('@ZikulaUsersModule/UserAdministration/searchResults.html.twig', [
                'deleteForm' => $deleteForm->createView(),
                'mailForm' => $this->buildMailForm()->createView()
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/mail")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function mailUsersAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::MailUsers', ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }
        $mailForm = $this->buildMailForm();
        $mailForm->handleRequest($request);
        if ($mailForm->isSubmitted() && $mailForm->isValid()) {
            $data = $mailForm->getData();
            $users = $this->get('zikula_users_module.user_repository')->query(['uid' => ['operator' => 'in', 'operand' => explode(',', $data['userIds'])]]);
            if (empty($users)) {
                throw new \InvalidArgumentException($this->__('No users found.'));
            }
            if ($this->get('zikula_users_module.helper.mail_helper')->mailUsers($users, $data)) {
                $this->addFlash('success', $this->__('Mail sent!'));
            } else {
                $this->addFlash('error', $this->__('Could not send mail.'));
            }
        } else {
            $this->addFlash('error', $this->__('Could not send mail.'));
        }

        return $this->redirectToRoute('zikulausersmodule_useradministration_search');
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function buildMailForm()
    {
        return $this->createForm('Zikula\UsersModule\Form\Type\MailType', [
            'from' => $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'sitename_' . \ZLanguage::getLanguageCode()),
            'replyto' => $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'adminmail'),
            'format' => 'text',
            'batchsize' => 100
        ], [
            'translator' => $this->get('translator.default'),
            'action' => $this->generateUrl('zikulausersmodule_useradministration_mailusers')
        ]);
    }
}
