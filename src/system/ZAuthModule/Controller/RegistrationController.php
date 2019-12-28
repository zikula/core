<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Helper\AccessHelper;
use Zikula\UsersModule\Helper\MailHelper;
use Zikula\UsersModule\Helper\RegistrationHelper;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Form\Type\VerifyRegistrationType;
use Zikula\ZAuthModule\Validator\Constraints\ValidRegistrationVerification;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class RegistrationController
 * @Route("")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/verify-registration/{uname}/{verifycode}")
     * @Template("@ZikulaZAuthModule/Registration/verify.html.twig")
     *
     * Render and process a registration e-mail verification code.
     *
     * This function will render and display to the user a form allowing him to enter
     * a verification code sent to him as part of the registration process. If the user's
     * registration does not have a password set (e.g., if an admin created the registration),
     * then he is prompted for it at this time. This function also processes the results of
     * that form, setting the registration record to verified (if appropriate), saving the password
     * (if provided) and if the registration record is also approved (or does not require it)
     * then a new user account is created
     *
     * @return array|RedirectResponse
     */
    public function verifyAction(
        Request $request,
        ValidatorInterface $validator,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        RegistrationHelper $registrationHelper,
        PasswordApiInterface $passwordApi,
        AccessHelper $accessHelper,
        MailHelper $mailHelper,
        string $uname = null,
        string $verifycode = null
    ) {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $setPass = false;
        // remove expired registrations
        $regExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION, ZAuthConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION);
        if ($regExpireDays > 0) {
            $deletedUsers = $userVerificationRepository->purgeExpiredRecords($regExpireDays);
            foreach ($deletedUsers as $deletedUser) {
                $this->get('event_dispatcher')->dispatch(new GenericEvent($deletedUser->getUid()), RegistrationEvents::DELETE_REGISTRATION);
            }
        }
        $codeValidationErrors = $validator->validate(['uname' => $uname, 'verifycode' => $verifycode], new ValidRegistrationVerification());
        if (count($codeValidationErrors) > 0) {
            $this->addFlash('warning', $this->__('The code provided is invalid or this user has never registered or has fully completed registration.'));

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        /** @var UserEntity $userEntity */
        $userEntity = $userRepository->findOneBy(['uname' => $uname]);
        if ($userEntity) {
            $mapping = $authenticationMappingRepository->getByZikulaId($userEntity->getUid());
            if ($mapping) {
                $setPass = null === $mapping->getPass() || '' === $mapping->getPass();
            }
        }
        $form = $this->createForm(VerifyRegistrationType::class, [
            'uname' => $uname,
            'verifycode' => $verifycode
        ], [
            'setpass' => $setPass
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userEntity = $userRepository->findOneBy(['uname' => $data['uname']]);
            $mapping = $authenticationMappingRepository->getByZikulaId($userEntity->getUid());
            if (isset($data['pass'])) {
                $mapping->setPass($passwordApi->getHashedPassword($data['pass']));
            }
            $mapping->setVerifiedEmail(true);
            $authenticationMappingRepository->persistAndFlush($mapping);
            $registrationHelper->registerNewUser($userEntity);
            $userVerificationRepository->resetVerifyChgFor($userEntity->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL);
            $adminNotificationEmail = $variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');

            switch ($userEntity->getActivated()) {
                case UsersConstant::ACTIVATED_PENDING_REG:
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($userEntity, true, !empty($adminNotificationEmail));
                    if (!empty($notificationErrors)) {
                        $this->addFlash('error', implode('<br />', $notificationErrors));
                    }
                    if ('' === $userEntity->getApproved_By()) {
                        $this->addFlash('status', $this->__('Done! Your account has been verified, and is awaiting administrator approval.'));
                    } else {
                        $this->addFlash('status', $this->__('Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.'));
                    }
                    break;
                case UsersConstant::ACTIVATED_ACTIVE:
                    $notificationErrors = $mailHelper->createAndSendUserMail($userEntity, true, !empty($adminNotificationEmail));
                    if (!empty($notificationErrors)) {
                        $this->addFlash('error', implode('<br />', $notificationErrors));
                    }
                    $accessHelper->login($userEntity);
                    $this->addFlash('status', $this->__('Done! Your account has been verified. You have been logged in.'));
                    break;
                default:
                    $this->addFlash('status', $this->__('Done! Your account has been verified.'));
                    $this->addFlash('status', $this->__('Your new account is not active yet. Please contact the site administrator for more information.'));
                    break;
            }

            return $this->redirectToRoute('home');
        }

        return [
            'form' => $form->createView(),
            'modvars' => $this->getVars()
        ];
    }
}
