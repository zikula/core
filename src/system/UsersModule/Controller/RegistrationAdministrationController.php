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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Container\HookContainer;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Entity\UserVerificationEntity;
use Zikula\UsersModule\RegistrationEvents;

/**
 * Class RegistrationAdministrationController
 * @Route("/admin/registration")
 */
class RegistrationAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{startnum}")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @param integer $startnum
     * @return array
     */
    public function listAction(Request $request, $startnum = 0)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $this->get('zikulausersmodule.helper.registration_helper')->purgeExpired();

        $limit = $this->getVar(UsersConstant::MODVAR_ITEMS_PER_PAGE, UsersConstant::DEFAULT_ITEMS_PER_PAGE);
        $users = $this->get('zikula_users_module.user_repository')->query(
            ['activated' => UsersConstant::ACTIVATED_PENDING_REG],
            ['user_regdate' => 'DESC'],
            $limit,
            $startnum
        );

        return [
            'moderationOrder' => $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE),
            'actionsHelper' => $this->get('zikula_users_module.helper.administration_actions'),
            'verificationRepo' => $this->get('zikula_users_module.user_verification_repository'),
            'pager' => [
                'count' => $users->count(),
                'limit' => $limit
            ],
            'users' => $users
        ];
    }

    /**
     * @Route("/display/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @param UserEntity $user
     * @return array
     */
    public function displayAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        /** @var UserVerificationEntity $verificationEntity */
        $verificationEntity = $this->get('zikula_users_module.user_verification_repository')->find($user->getUid());

        return [
            'user' => $user,
            'verificationSent' => empty($verificationEntity) ? false : $verificationEntity->getCreated_Dt(),
            'validUntil' => $user->isVerified() ? false : $this->registrationValidUntil($verificationEntity),
            'actions' => $this->get('zikula_users_module.helper.administration_actions')->registration($user)
        ];
    }

    /**
     * @Route("/modify/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @return Response
     */
    public function modifyAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', $user->getUname() . "::" . $user->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\UsersModule\Form\Type\ModifyRegistrationType',
            $user, ['translator' => $this->get('translator.default')]
        );

        $originalUser = clone $user;
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_VALIDATE_MODIFY, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(HookContainer::HOOK_REGISTRATION_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                /** @var UserEntity $user */
                $user = $form->getData();
                $this->get('doctrine')->getManager()->flush($user);
                $eventArgs = [
                    'action' => 'setVar',
                    'field' => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalUser->getUname()];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $this->get('event_dispatcher')->dispatch(RegistrationEvents::UPDATE_REGISTRATION, $updateEvent);
                if ($user->getEmail() != $originalUser->getEmail()) {
                    $approvalOrder = $this->getVar('moderation_order', UsersConstant::APPROVAL_BEFORE);
                    if (!(bool)$user->getAttributeValue('_Users_isVerified') && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $originalUser->isApproved())) {
                        $verificationSent = $this->get('zikulausersmodule.helper.registration_verification_helper')->sendVerificationCode(null, $user->getUid(), true);
                        if (!$verificationSent) {
                            $this->addFlash('error', $this->__('Could not resend verification code.'));
                        }
                    }
                }
                $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_PROCESS_MODIFY, new GenericEvent($user));
                $this->get('hook_dispatcher')->dispatch(HookContainer::HOOK_REGISTRATION_PROCESS, new ProcessHook($user->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));

                return $this->redirectToRoute('zikulausersmodule_admin_displayregistration', ['uid' => $user->getUid()]);
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/verify/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @return array
     */
    public function verifyAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm('Zikula\UsersModule\Form\Type\SendVerificationConfirmationType', [
            'user' => $user->getUid()
        ], [
            'translator' => $this->get('translator.default')
        ]);
        $approvalOrder = $this->getVar('moderation_order', UsersConstant::APPROVAL_BEFORE);
        if ($user->isVerified()) {
            $this->addFlash('error', $this->__f('Error! A verification code cannot be sent for the registration record for %sub%. It is already verified.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        } elseif (($approvalOrder == UsersConstant::APPROVAL_BEFORE) && !$user->isApproved()) {
            $this->addFlash('error', $this->__f('Error! A verification code cannot be sent for the registration record for %sub%. It must first be approved.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $verificationSent = $this->get('zikulausersmodule.helper.registration_verification_helper')->sendVerificationCode($user);
                if (!$verificationSent) {
                    $this->addFlash('error', $this->__f('Sorry! There was a problem sending a verification code to %sub%.', ['%sub%' => $user->getUname()]));
                } else {
                    $this->addFlash('status', $this->__f('Done! Verification code sent to %sub%.', ['%sub%' => $user->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        }
        /** @var UserVerificationEntity $verificationEntity */
        $verificationEntity = $this->get('zikula_users_module.user_verification_repository')->find($user->getUid());

        return [
            'form' => $form->createView(),
            'validUntil' => $user->isVerified() ? false : $this->registrationValidUntil($verificationEntity),
            'verificationSent' => empty($verificationEntity) ? false : $verificationEntity->getCreated_Dt(),
            'user' => $user
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
        $form = $this->createForm('Zikula\UsersModule\Form\Type\ApproveRegistrationConfirmationType', [
            'user' => $user->getUid(),
            'force' => $forceVerification
        ], [
            'translator' => $this->get('translator.default'),
            'buttonLabel' => $force && !$user->isVerified() ? $this->__('Skip verification and approve') : $this->__('Approve')
        ]);
        $approvalOrder = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::APPROVAL_BEFORE);
        if ($user->isApproved() && !$forceVerification) {
            $this->addFlash('error', $this->__f('Warning! Nothing to do! The registration record for %sub% is already approved.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        } elseif (!$forceVerification && ($approvalOrder == UsersConstant::APPROVAL_AFTER) && !$user->isApproved()
            && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $this->addFlash('error', $this->__f('Error! The registration record for %sub% cannot be approved. The registration\'s e-mail address must first be verified.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        } elseif ($forceVerification && (null == $user->getPass() || '' == $user->getPass())) {
            $this->addFlash('error', $this->__f('Error! E-mail verification cannot be skipped for %sub%. The user must establish a password as part of the verification process.', ['%sub%' => $user->getUname()]));

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $denied = $this->get('zikulausersmodule.helper.registration_helper')->approve($user, true);
                if (!$denied) {
                    $this->addFlash('error', $this->__f('Sorry! There was a problem approving the registration for %sub%.', ['%sub%' => $user->getUname()]));
                } else {
                    if (null != $user->getUid()) {
                        $this->addFlash('status', $this->__f('Done! The registration for %sub% has been approved and a new user account has been created.', ['%sub%' => $user->getUname()]));
                    } else {
                        $this->addFlash('status', $this->__f('Done! The registration for %sub% has been approved and is awaiting e-mail verification.', ['%sub%' => $user->getUname()]));
                    }
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        }
        /** @var UserVerificationEntity $verificationEntity */
        $verificationEntity = $this->get('zikula_users_module.user_verification_repository')->find($user->getUid());

        return [
            'form' => $form->createView(),
            'validUntil' => $user->isVerified() ? false : $this->registrationValidUntil($verificationEntity),
            'verificationSent' => empty($verificationEntity) ? false : $verificationEntity->getCreated_Dt(),
            'user' => $user
        ];
    }

    /**
     * @Route("/deny/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @return array
     */
    public function denyAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm('Zikula\UsersModule\Form\Type\DenyRegistrationConfirmationType', [
            'user' => $user->getUid()
        ], [
            'translator' => $this->get('translator.default')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $denied = $this->get('zikulausersmodule.helper.registration_helper')->remove($user->getUid());
                if (!$denied) {
                    $this->addFlash('error', $this->__f('Sorry! There was a problem deleting the registration for %sub%.', ['%sub%' => $user->getUname()]));
                } else {
                    $data = $form->getData();
                    if ($data['notify']) {
                        $rendererArgs = array(
                            'user' => $user,
                            'reason' => $data['reason'],
                        );
                        $this->get('zikulausersmodule.helper.mail_helper')->sendNotification($user->getEmail(), 'regdeny', $rendererArgs);
                    }
                    $this->addFlash('status', $this->__f('Done! The registration for %sub% has been denied and deleted.', ['%sub%' => $user->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_registrationadministration_list');
        }

        /** @var UserVerificationEntity $verificationEntity */
        $verificationEntity = $this->get('zikula_users_module.user_verification_repository')->find($user->getUid());

        return [
            'form' => $form->createView(),
            'validUntil' => $user->isVerified() ? false : $this->registrationValidUntil($verificationEntity),
            'verificationSent' => empty($verificationEntity) ? false : $verificationEntity->getCreated_Dt(),
            'user' => $user
        ];
    }

    /**
     * @param UserVerificationEntity $verificationEntity
     * @return bool|string
     */
    private function registrationValidUntil(UserVerificationEntity $verificationEntity)
    {
        $regExpireDays = $this->getVar('reg_expiredays', 0);
        $validUntil = false;
        if (!empty($verificationEntity) && ($regExpireDays > 0)) {
            try {
                $expiresUTC = new \DateTime($verificationEntity->getCreated_Dt(), new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                $expiresUTC = new \DateTime(UsersConstant::EXPIRED, new \DateTimeZone('UTC'));
            }
            $expiresUTC->modify("+{$regExpireDays} days");
            $validUntil = \DateUtil::formatDatetime($expiresUTC->format(UsersConstant::DATETIME_FORMAT),
                $this->__('%m-%d-%Y %H:%M'));
        }

        return $validUntil;
    }
}
