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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Helper\AccessHelper;
use Zikula\UsersBundle\Helper\MailHelper;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\Form\Type\VerifyRegistrationType;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\Validator\Constraints\ValidRegistrationVerification;
use Zikula\ZAuthBundle\ZAuthConstant;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly int $minimumPasswordLength,
        private readonly bool $usePasswordStrengthMeter
    ) {
    }

    /**
     * Render and process a registration e-mail verification code.
     *
     * This function will render and display to the user a form allowing him to enter
     * a verification code sent to him as part of the registration process. If the user's
     * registration does not have a password set (e.g., if an admin created the registration),
     * then he is prompted for it at this time. This function also processes the results of
     * that form, setting the registration record to verified (if appropriate), saving the password
     * (if provided) and if the registration record is also approved (or does not require it)
     * then a new user account is created.
     */
    #[Route('/verify-registration/{uname}/{verifycode}', name: 'zikulazauthbundle_registration_verify')]
    public function verify(
        Request $request,
        ValidatorInterface $validator,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        RegistrationHelper $registrationHelper,
        EncoderFactoryInterface $encoderFactory,
        AccessHelper $accessHelper,
        MailHelper $mailHelper,
        string $uname = null,
        string $verifycode = null
    ): Response {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        $setPass = false;
        $codeValidationErrors = $validator->validate(
            [
                'uname' => $uname,
                'verifycode' => $verifycode,
            ],
            new ValidRegistrationVerification()
        );
        if (0 < count($codeValidationErrors)) {
            $this->addFlash('warning', 'The code provided is invalid or this user has never registered or has fully completed registration.');

            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }

        /** @var User $userEntity */
        $userEntity = $userRepository->findOneBy(['uname' => $uname]);
        if ($userEntity) {
            $mapping = $authenticationMappingRepository->getByZikulaId($userEntity->getUid());
            if ($mapping) {
                $setPass = null === $mapping->getPass() || '' === $mapping->getPass();
            }
        }
        $form = $this->createForm(VerifyRegistrationType::class, [
            'uname' => $uname,
            'verifycode' => $verifycode,
        ], [
            'minimumPasswordLength' => $this->minimumPasswordLength,
            'setpass' => $setPass,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userEntity = $userRepository->findOneBy(['uname' => $data['uname']]);
            $mapping = $authenticationMappingRepository->getByZikulaId($userEntity->getUid());
            if (isset($data['pass'])) {
                $mapping->setPass($encoderFactory->getEncoder($mapping)->encodePassword($data['pass'], null));
            }
            $mapping->setVerifiedEmail(true);
            $authenticationMappingRepository->persistAndFlush($mapping);
            $registrationHelper->registerNewUser($userEntity);
            $userVerificationRepository->resetVerifyChgFor($userEntity->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL);
            $adminNotificationEmail = $registrationHelper->getNotificationEmail();

            switch ($userEntity->getActivated()) {
                case UsersConstant::ACTIVATED_PENDING_REG:
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($userEntity, true, !empty($adminNotificationEmail));
                    if (!empty($notificationErrors)) {
                        $this->addFlash('error', implode('<br />', $notificationErrors));
                    }
                    if ('' === $userEntity->getApprovedBy()) {
                        $this->addFlash('status', 'Done! Your account has been verified, and is awaiting administrator approval.');
                    } else {
                        $this->addFlash('status', 'Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.');
                    }
                    break;
                case UsersConstant::ACTIVATED_ACTIVE:
                    $notificationErrors = $mailHelper->createAndSendUserMail($userEntity, true, !empty($adminNotificationEmail));
                    if (!empty($notificationErrors)) {
                        $this->addFlash('error', implode('<br />', $notificationErrors));
                    }
                    $accessHelper->login($userEntity);
                    $this->addFlash('status', 'Done! Your account has been verified. You have been logged in.');
                    break;
                default:
                    $this->addFlash('status', 'Done! Your account has been verified.');
                    $this->addFlash('status', 'Your new account is not active yet. Please contact the site administrator for more information.');
                    break;
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('@ZikulaZAuth/Registration/verify.html.twig', [
            'form' => $form->createView(),
            'usePasswordStrengthMeter' => $this->usePasswordStrengthMeter,
        ]);
    }
}
