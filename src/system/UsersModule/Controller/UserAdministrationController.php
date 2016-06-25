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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $filter['activated'] = ['operator' => 'notIn', 'operand' => [
            UsersConstant::ACTIVATED_PENDING_REG,
            UsersConstant::ACTIVATED_PENDING_DELETE
        ]];
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
     * @todo not sure this method should be kept or moved to ZAuth
     * @Route("/send-username/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @param Request $request
     * @param UserEntity $user
     * @return RedirectResponse
     */
    public function sendUserNameAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $mailSent = $this->get('zikula_users_module.helper.mail_helper')->mailUserName($user, true);
        if ($mailSent) {
            $this->addFlash('status', $this->__f('Done! The user name for %s has been sent via e-mail.', ['%s' => $user->getUname()]));
        }

        return $this->redirectToRoute('zikulausersmodule_useradministration_list');
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
