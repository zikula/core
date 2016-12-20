<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Controller;

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
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Container\HookContainer;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Form\Type\AdminCreatedUserType;
use Zikula\ZAuthModule\Form\Type\AdminModifyUserType;
use Zikula\ZAuthModule\Form\Type\SendVerificationConfirmationType;
use Zikula\ZAuthModule\Form\Type\TogglePasswordConfirmationType;
use Zikula\ZAuthModule\ZAuthConstant;

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
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $startnum = $startnum > 0 ? $startnum - 1 : 0;

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulazauthmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'startnum' => $startnum
        ]);

        $filter = [];
        if (!empty($letter) && 'all' != $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "$letter%"];
        }
        $limit = 25; // @todo make this configurable?

        $mappings = $this->get('zikula_zauth_module.authentication_mapping_repository')->query(
            $filter,
            [$sort => $sortdir],
            $limit,
            $startnum
        );

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => [
                'count' => $mappings->count(),
                'limit' => $limit
            ],
            'actionsHelper' => $this->get('zikula_zauth_module.helper.administration_actions_helper'),
            'mappings' => $mappings
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
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'uname' => ['operator' => 'like', 'operand' => "$fragment%"]
        ];
        $mappings = $this->get('zikula_zauth_module.authentication_mapping_repository')->query($filter);

        return $this->render('@ZikulaZAuthModule/UserAdministration/userlist.html.twig', [
            'mappings' => $mappings,
            'actionsHelper' => $this->get('zikula_zauth_module.helper.administration_actions_helper'),
        ], new PlainResponse());
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
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $mapping = new AuthenticationMappingEntity();
        $form = $this->createForm(AdminCreatedUserType::class, $mapping, [
            'translator' => $this->get('translator.default')
        ]);
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), [], new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(UserEvents::NEW_VALIDATE, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(HookContainer::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $mapping = $form->getData();
                $passToSend = $form['sendpass']->getData() ? $mapping->getPass() : '';
                $authMethodName = ($mapping->getMethod() == ZAuthConstant::AUTHENTICATION_METHOD_EITHER) ? ZAuthConstant::AUTHENTICATION_METHOD_UNAME : $mapping->getMethod();
                $authMethod = $this->get('zikula_users_module.internal.authentication_method_collector')->get($authMethodName);
                $user = new UserEntity();
                $user->merge($mapping->getUserEntityData());
                $user->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                $this->get('zikula_users_module.helper.registration_helper')->registerNewUser($user);
                if ($user->getActivated() == UsersConstant::ACTIVATED_PENDING_REG) {
                    $notificationErrors = $this->get('zikula_users_module.helper.mail_helper')->createAndSendRegistrationMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                } else {
                    $notificationErrors = $this->get('zikula_users_module.helper.mail_helper')->createAndSendUserMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                }
                if (!empty($notificationErrors)) {
                    $this->addFlash('error', $this->__('Errors creating user!'));
                    $this->addFlash('error', implode('<br>', $notificationErrors));
                }
                $mapping->setUid($user->getUid());
                if (!$authMethod->register($mapping->toArray())) {
                    $this->addFlash('error', $this->__('The create process failed for an unknown reason.'));
                    $this->get('zikula_users_module.user_repository')->removeAndFlush($user);
                    $this->get('event_dispatcher')->dispatch(RegistrationEvents::DELETE_REGISTRATION, new GenericEvent($user->getUid()));

                    return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
                }
                $event = new GenericEvent($form->getData(), [], new ValidationProviders());
                $this->get('event_dispatcher')->dispatch(UserEvents::NEW_PROCESS, $event);
                $hook = new ProcessHook($user->getUid());
                $this->get('hook_dispatcher')->dispatch(HookContainer::EDIT_PROCESS, $hook);
                $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_SUCCEEDED, new GenericEvent($user));

                if ($user->getActivated() == UsersConstant::ACTIVATED_PENDING_REG) {
                    $this->addFlash('status', $this->__('Done! Created new registration application.'));
                } elseif (null !== $user->getActivated()) {
                    $this->addFlash('status', $this->__('Done! Created new user account.'));
                } else {
                    $this->addFlash('error', $this->__('Warning! New user information has been saved, however there may have been an issue saving it properly.'));
                }

                return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
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
     * @Route("/user/modify/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function modifyAction(Request $request, AuthenticationMappingEntity $mapping)
    {
        if (!$this->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . "::" . $mapping->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (1 === $mapping->getUid()) {
            throw new AccessDeniedException($this->__("Error! You can't edit the guest account."));
        }

        $form = $this->createForm(AdminModifyUserType::class, $mapping, [
            'translator' => $this->get('translator.default')
        ]);
        $originalMapping = clone $mapping;
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), [], new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(UserEvents::MODIFY_VALIDATE, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(HookContainer::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                /** @var AuthenticationMappingEntity $mapping */
                $mapping = $form->getData();
                if ($form->get('setpass')->getData()) {
                    $mapping->setPass($this->get('zikula_zauth_module.api.password')->getHashedPassword($mapping->getPass()));
                } else {
                    $mapping->setPass($originalMapping->getPass());
                }
                $this->get('zikula_zauth_module.authentication_mapping_repository')->persistAndFlush($mapping);
                $userEntity = $this->get('zikula_users_module.user_repository')->find($mapping->getUid());
                $userEntity->merge($mapping->getUserEntityData());
                $this->get('zikula_users_module.user_repository')->persistAndFlush($userEntity);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalMapping->getUname()];
                $updateEvent = new GenericEvent($userEntity, $eventArgs, $eventData);
                $this->get('event_dispatcher')->dispatch(UserEvents::UPDATE_ACCOUNT, $updateEvent);

                $this->get('event_dispatcher')->dispatch(UserEvents::MODIFY_PROCESS, new GenericEvent($userEntity));
                $this->get('hook_dispatcher')->dispatch(HookContainer::EDIT_PROCESS, new ProcessHook($mapping->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/verify/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @return array
     */
    public function verifyAction(Request $request, AuthenticationMappingEntity $mapping)
    {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(SendVerificationConfirmationType::class, [
            'mapping' => $mapping->getId()
        ], [
            'translator' => $this->get('translator.default')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->find($form->get('mapping')->getData());
                $verificationSent = $this->get('zikula_zauth_module.helper.registration_verification_helper')->sendVerificationCode($mapping);
                if (!$verificationSent) {
                    $this->addFlash('error', $this->__f('Sorry! There was a problem sending a verification code to %sub%.', ['%sub%' => $mapping->getUname()]));
                } else {
                    $this->addFlash('status', $this->__f('Done! Verification code sent to %sub%.', ['%sub%' => $mapping->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'mapping' => $mapping
        ];
    }

    /**
     * @Route("/send-confirmation/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @return RedirectResponse
     */
    public function sendConfirmationAction(Request $request, AuthenticationMappingEntity $mapping)
    {
        if (!$this->hasPermission('ZikulaZAuthModule', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $changePasswordExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
        $lostPasswordId = $this->get('zikula_zauth_module.helper.lost_password_verification_helper')->createLostPasswordId($mapping);
        $mailSent = $this->get('zikula_zauth_module.helper.mail_helper')->sendNotification($mapping->getEmail(), 'lostpassword', [
            'uname' => $mapping->getUname(),
            'validDays' => $changePasswordExpireDays,
            'lostPasswordId' => $lostPasswordId,
            'requestedByAdmin' => true
        ]);
        if ($mailSent) {
            $this->addFlash('status', $this->__f('Done! The password recovery verification link for %s has been sent via e-mail.', ['%s' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
    }

    /**
     * @Route("/send-username/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @return RedirectResponse
     */
    public function sendUserNameAction(Request $request, AuthenticationMappingEntity $mapping)
    {
        if (!$this->hasPermission('ZikulaZAuthModule', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $mailSent = $this->get('zikula_zauth_module.helper.mail_helper')->sendNotification($mapping->getEmail(), 'lostuname', [
            'uname' => $mapping->getUname(),
            'requestedByAdmin' => true,
        ]);

        if ($mailSent) {
            $this->addFlash('status', $this->__f('Done! The user name for %s has been sent via e-mail.', ['%s' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
    }

    /**
     * @Route("/toggle-password-change/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @param UserEntity $user // note: this is intentionally left as UserEntity instead of mapping because of need to access attributes
     * @return array|RedirectResponse
     */
    public function togglePasswordChangeAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaZAuthModule', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
            $mustChangePass = $user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
        } else {
            $mustChangePass = false;
        }
        $form = $this->createForm(TogglePasswordConfirmationType::class, [
            'uid' => $user->getUid(),
        ], [
            'mustChangePass' => $mustChangePass,
            'translator' => $this->get('translator.default')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('toggle')->isClicked()) {
                if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY) && (bool)$user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
                    $user->getAttributes()->remove(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
                    $this->addFlash('success', $this->__f('Done! A password change will no longer be required for %uname.', ['%uname' => $user->getUname()]));
                } else {
                    $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
                    $this->addFlash('success', $this->__f('Done! A password change will be required the next time %uname logs in.', ['%uname' => $user->getUname()]));
                }
                $this->get('doctrine')->getManager()->flush();
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'mustChangePass' => $mustChangePass,
            'user' => $user
        ];
    }
}
