<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Filter\AlphaFilter;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Collector\AuthenticationMethodCollector;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Event\ActiveUserPostUpdatedEvent;
use Zikula\UsersBundle\Event\EditUserFormPostCreatedEvent;
use Zikula\UsersBundle\Event\EditUserFormPostValidatedEvent;
use Zikula\UsersBundle\Event\RegistrationPostDeletedEvent;
use Zikula\UsersBundle\Event\RegistrationPostSuccessEvent;
use Zikula\UsersBundle\Helper\MailHelper as UsersMailHelper;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthBundle\Form\Type\AdminCreatedUserType;
use Zikula\ZAuthBundle\Form\Type\AdminModifyUserType;
use Zikula\ZAuthBundle\Form\Type\BatchForcePasswordChangeType;
use Zikula\ZAuthBundle\Form\Type\SendVerificationConfirmationType;
use Zikula\ZAuthBundle\Form\Type\TogglePasswordConfirmationType;
use Zikula\ZAuthBundle\Helper\AdministrationActionsHelper;
use Zikula\ZAuthBundle\Helper\BatchPasswordChangeHelper;
use Zikula\ZAuthBundle\Helper\LostPasswordVerificationHelper;
use Zikula\ZAuthBundle\Helper\MailHelper;
use Zikula\ZAuthBundle\Helper\RegistrationVerificationHelper;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

#[Route('/zauth/admin')]
class UserAdministrationController extends AbstractController
{
    use TranslatorTrait;

    public function __construct(
        TranslatorInterface $translator,
        private readonly PermissionApiInterface $permissionApi,
        private readonly int $usersPerPage,
        private readonly int $minimumPasswordLength,
        private readonly bool $usePasswordStrengthMeter,
        private readonly int $changePasswordExpireDays
    ) {
        $this->setTranslator($translator);
    }

    /**
     * @PermissionCheck("moderate")
     * @Theme("admin")
     */
    #[Route('/list/{sort}/{sortdir}/{letter}/{page}', name: 'zikulazauthbundle_useradministration_listmappings', methods: ['GET'], requirements: ['page' => '\d+'])]
    public function listMappings(
        Request $request,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        RouterInterface $router,
        AdministrationActionsHelper $actionsHelper,
        string $sort = 'uid',
        string $sortdir = 'DESC',
        string $letter = 'all',
        int $page = 1
    ): Response {
        $sortableColumns = new SortableColumns($router, 'zikulazauthbundle_useradministration_listmappings', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'page' => $page
        ]);

        $filter = [];
        if (!empty($letter) && 'all' !== $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "${letter}%"];
        }
        $paginator = $authenticationMappingRepository->query($filter, [$sort => $sortdir], 'and', $page, $this->usersPerPage);
        $paginator->setRoute('zikulazauthbundle_useradministration_listmappings');
        $routeParameters = [
            'sort' => $sort,
            'sortdir' => $sortdir,
            'letter' => $letter,
        ];
        $paginator->setRouteParameters($routeParameters);

        return $this->render('@ZikulaZAuth/UserAdministration/list.html.twig', [
            'sort' => $sortableColumns->generateSortableColumns(),
            'actionsHelper' => $actionsHelper,
            'alpha' => new AlphaFilter('zikulazauthbundle_useradministration_listmappings', $routeParameters, $letter),
            'paginator' => $paginator,
        ]);
    }

    /**
     * Called from UsersBundle/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     */
    #[Route('/getusersbyfragmentastable', name: 'zikulazauthbundle_useradministration_getusersbyfragmentastable', methods: ['POST'], options: ['expose' => true])]
    public function getUsersByFragmentAsTable(
        Request $request,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        AdministrationActionsHelper $actionsHelper
    ): Response {
        if (!$this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'uname' => ['operator' => 'like', 'operand' => $fragment . '%']
        ];
        $mappings = $authenticationMappingRepository->query($filter);

        return $this->render('@ZikulaZAuth/UserAdministration/userlist.html.twig', [
            'mappings' => $mappings->getResults(),
            'actionsHelper' => $actionsHelper,
        ], new PlainResponse());
    }

    /**
     * @PermissionCheck("admin")
     * @Theme("admin")
     */
    #[Route('/user/create', name: 'zikulazauthbundle_useradministration_create')]
    public function create(
        Request $request,
        AuthenticationMethodCollector $authenticationMethodCollector,
        UserRepositoryInterface $userRepository,
        RegistrationHelper $registrationHelper,
        UsersMailHelper $mailHelper,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $mapping = new AuthenticationMappingEntity();
        $form = $this->createForm(AdminCreatedUserType::class, $mapping, [
            'minimumPasswordLength' => $this->minimumPasswordLength,
        ]);
        $editUserFormPostCreatedEvent = new EditUserFormPostCreatedEvent($form);
        $eventDispatcher->dispatch($editUserFormPostCreatedEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                $mapping = $form->getData();
                $passToSend = $form['sendpass']->getData() ? $mapping->getPass() : '';
                $authMethodName = (ZAuthConstant::AUTHENTICATION_METHOD_EITHER === $mapping->getMethod()) ? ZAuthConstant::AUTHENTICATION_METHOD_UNAME : $mapping->getMethod();
                $authMethod = $authenticationMethodCollector->get($authMethodName);

                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->set(ZAuthConstant::SESSION_EMAIL_VERIFICATION_STATE, ($form['usermustverify']->getData() ? 'Y' : 'N'));
                }

                $userData = $mapping->getUserEntityData();
                if (null === $userData['uid']) {
                    unset($userData['uid']);
                }
                $user = new UserEntity();
                foreach ($userData as $fieldName => $fieldValue) {
                    $setter = 'set' . ucfirst($fieldName);
                    $user->{$setter}($fieldValue);
                }
                $user->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                $registrationHelper->registerNewUser($user);
                if (UsersConstant::ACTIVATED_PENDING_REG === $user->getActivated()) {
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                } else {
                    $notificationErrors = $mailHelper->createAndSendUserMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                }
                if (!empty($notificationErrors)) {
                    $this->addFlash('error', 'Errors creating user!');
                    $this->addFlash('error', implode('<br />', $notificationErrors));
                }
                $mapping->setUid($user->getUid());
                $mapping->setVerifiedEmail(!$form['usermustverify']->getData());
                if (!$authMethod->register($mapping->toArray())) {
                    $this->addFlash('error', 'The create process failed for an unknown reason.');
                    $userRepository->removeAndFlush($user);
                    $eventDispatcher->dispatch(new RegistrationPostDeletedEvent($user));

                    return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
                }
                $eventDispatcher->dispatch(new EditUserFormPostValidatedEvent($form, $user));
                $eventDispatcher->dispatch(new RegistrationPostSuccessEvent($user));

                if (UsersConstant::ACTIVATED_PENDING_REG === $user->getActivated()) {
                    $this->addFlash('status', 'Done! Created new registration application.');
                } elseif (null !== $user->getActivated()) {
                    $this->addFlash('status', 'Done! Created new user account.');
                } else {
                    $this->addFlash('error', 'Warning! New user information has been saved, however there may have been an issue saving it properly.');
                }

                return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return $this->render('@ZikulaZAuth/UserAdministration/create.html.twig', [
            'form' => $form->createView(),
            'additionalTemplates' => isset($editUserFormPostCreatedEvent) ? $editUserFormPostCreatedEvent->getTemplates() : [],
            'usePasswordStrengthMeter' => $this->usePasswordStrengthMeter,
        ]);
    }

    /**
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user hasn't edit permissions for the mapping record
     */
    #[Route('/user/modify/{mapping}', name: 'zikulazauthbundle_useradministration_modify', requirements: ['mapping' => '^[1-9]\d*$'])]
    public function modify(
        Request $request,
        AuthenticationMappingEntity $mapping,
        EncoderFactoryInterface $encoderFactory,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        if (!$this->permissionApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (1 === $mapping->getUid()) {
            throw new AccessDeniedException($this->trans("Error! You can't edit the guest account."));
        }

        $form = $this->createForm(AdminModifyUserType::class, $mapping, [
            'minimumPasswordLength' => $this->minimumPasswordLength,
        ]);
        $originalMapping = clone $mapping;
        $editUserFormPostCreatedEvent = new EditUserFormPostCreatedEvent($form);
        $eventDispatcher->dispatch($editUserFormPostCreatedEvent);
        $form->handleRequest($request);

        $originalUser = clone $userRepository->find($mapping->getUid());

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                /** @var AuthenticationMappingEntity $mapping */
                $mapping = $form->getData();
                if ($form->get('setpass')->getData()) {
                    $mapping->setPass($encoderFactory->getEncoder($mapping)->encodePassword($mapping->getPass(), null));
                } else {
                    $mapping->setPass($originalMapping->getPass());
                }
                $authenticationMappingRepository->persistAndFlush($mapping);
                $userData = $mapping->getUserEntityData();
                /** @var UserEntity $user */
                $user = $userRepository->find($mapping->getUid());
                foreach ($userData as $fieldName => $fieldValue) {
                    $setter = 'set' . ucfirst($fieldName);
                    $user->{$setter}($fieldValue);
                }
                $userRepository->persistAndFlush($user);

                $eventDispatcher->dispatch(new ActiveUserPostUpdatedEvent($user, $originalUser));

                $eventDispatcher->dispatch(new EditUserFormPostValidatedEvent($form, $user));

                $this->addFlash('status', "Done! Saved user's account information.");
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
        }

        return $this->render('@ZikulaZAuth/UserAdministration/modify.html.twig', [
            'form' => $form->createView(),
            'additionalTemplates' => isset($editUserFormPostCreatedEvent) ? $editUserFormPostCreatedEvent->getTemplates() : [],
            'usePasswordStrengthMeter' => $this->usePasswordStrengthMeter,
        ]);
    }

    /**
     * @PermissionCheck("moderate")
     * @Theme("admin")
     */
    #[Route('/verify/{mapping}', name: 'zikulazauthbundle_useradministration_verify', requirements: ['mapping' => '^[1-9]\d*$'])]
    public function verify(
        Request $request,
        AuthenticationMappingEntity $mapping,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        RegistrationVerificationHelper $registrationVerificationHelper
    ): Response {
        $form = $this->createForm(SendVerificationConfirmationType::class, [
            'mapping' => $mapping->getId()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                /** @var AuthenticationMappingEntity $modifiedMapping */
                $modifiedMapping = $authenticationMappingRepository->find($form->get('mapping')->getData());
                $verificationSent = $registrationVerificationHelper->sendVerificationCode($modifiedMapping);
                if (!$verificationSent) {
                    $this->addFlash('error', $this->trans('Sorry! There was a problem sending a verification code to %sub%.', ['%sub%' => $modifiedMapping->getUname()]));
                } else {
                    $this->addFlash('status', $this->trans('Done! Verification code sent to %sub%.', ['%sub%' => $modifiedMapping->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
        }

        return $this->render('@ZikulaZAuth/UserAdministration/verify.html.twig', [
            'form' => $form->createView(),
            'mapping' => $mapping,
        ]);
    }

    /**
     * @throws AccessDeniedException Thrown if the user hasn't moderate permissions for the mapping record
     */
    #[Route('/send-confirmation/{mapping}', name: 'zikulazauthbundle_useradministration_sendconfirmation', requirements: ['mapping' => '^[1-9]\d*$'])]
    public function sendConfirmation(
        AuthenticationMappingEntity $mapping,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        MailHelper $mailHelper
    ): RedirectResponse {
        if (!$this->permissionApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $lostPasswordId = $lostPasswordVerificationHelper->createLostPasswordId($mapping);
        $mailSent = $mailHelper->sendNotification($mapping->getEmail(), 'lostpassword', [
            'uname' => $mapping->getUname(),
            'validDays' => $this->changePasswordExpireDays,
            'lostPasswordId' => $lostPasswordId,
            'requestedByAdmin' => true,
        ]);
        if ($mailSent) {
            $this->addFlash('status', $this->trans('Done! The password recovery verification link for %userName% has been sent via e-mail.', ['%userName%' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
    }

    /**
     * @throws AccessDeniedException Thrown if the user hasn't moderate permissions for the mapping record
     */
    #[Route('/send-username/{mapping}', name: 'zikulazauthbundle_useradministration_sendusername', requirements: ['mapping' => '^[1-9]\d*$'])]
    public function sendUserName(
        AuthenticationMappingEntity $mapping,
        MailHelper $mailHelper
    ): RedirectResponse {
        if (!$this->permissionApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $mailSent = $mailHelper->sendNotification($mapping->getEmail(), 'lostuname', [
            'uname' => $mapping->getUname(),
            'requestedByAdmin' => true,
        ]);

        if ($mailSent) {
            $this->addFlash('status', $this->trans('Done! The user name for %userName% has been sent via e-mail.', ['%userName%' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
    }

    /**
     * @Theme("admin")
     *
     * @param UserEntity $user // note: this is intentionally left as UserEntity instead of mapping because of need to access attributes
     *
     * @throws AccessDeniedException Thrown if the user hasn't moderate permissions for the user record
     */
    #[Route('/toggle-password-change/{user}', name: 'zikulazauthbundle_useradministration_togglepasswordchange', requirements: ['user' => '^[1-9]\d*$'])]
    public function togglePasswordChange(Request $request, ManagerRegistry $doctrine, UserEntity $user)
    {
        if (!$this->permissionApi->hasPermission('ZikulaZAuthModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
            $mustChangePass = $user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
        } else {
            $mustChangePass = false;
        }
        $form = $this->createForm(TogglePasswordConfirmationType::class, [
            'uid' => $user->getUid()
        ], [
            'mustChangePass' => $mustChangePass
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('toggle')->isClicked()) {
                if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY) && (bool) $user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
                    $user->getAttributes()->remove(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
                    $this->addFlash('success', $this->trans('Done! A password change will no longer be required for %userName%.', ['%userName%' => $user->getUname()]));
                } else {
                    $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
                    $this->addFlash('success', $this->trans('Done! A password change will be required the next time %userName% logs in.', ['%userName%' => $user->getUname()]));
                }
                $doctrine->getManager()->flush();
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
        }

        return $this->render('@ZikulaZAuth/UserAdministration/togglePasswordChange.html.twig', [
            'form' => $form->createView(),
            'mustChangePass' => $mustChangePass,
            'user' => $user,
        ]);
    }

    /**
     * @PermissionCheck("admin")
     * @Theme("admin")
     */
    #[Route('/batch-force-password-change', name: 'zikulazauthbundle_useradministration_batchforcepasswordchange')]
    public function batchForcePasswordChange(
        BatchPasswordChangeHelper $batchPasswordChangeHelper,
        Request $request
    ): Response {
        $form = $this->createForm(BatchForcePasswordChangeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                $count = $batchPasswordChangeHelper->requirePasswordChangeByGroup($form->get('group')->getData());
                $this->addFlash('success', $this->trans('Operation complete. %count% user(s) changed.', ['%count%' => $count]));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
        }

        return $this->render('@ZikulaZAuth/UserAdministration/batchForcePasswordChange.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
