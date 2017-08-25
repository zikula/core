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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\RegistrationEvents;
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
     * @Template
     * @param Request $request
     * @param null|string $uname
     * @param null|string $verifycode
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
    public function verifyAction(Request $request, $uname = null, $verifycode = null)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $setPass = false;
        // remove expired registrations
        $regExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION, ZAuthConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION);
        if ($regExpireDays > 0) {
            $deletedUsers = $this->get('zikula_zauth_module.user_verification_repository')->purgeExpiredRecords($regExpireDays);
            foreach ($deletedUsers as $deletedUser) {
                $this->get('event_dispatcher')->dispatch(RegistrationEvents::DELETE_REGISTRATION, new GenericEvent($deletedUser->getUid()));
            }
        }
        $codeValidationErrors = $this->get('validator')->validate(['uname' => $uname, 'verifycode' => $verifycode], new ValidRegistrationVerification());
        if (count($codeValidationErrors) > 0) {
            $this->addFlash('warning', $this->__('The code provided is invalid or this user has never registered or has fully completed registration.'));

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $userEntity = $this->get('zikula_users_module.user_repository')->findOneBy(['uname' => $uname]);
        if ($userEntity) {
            $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->getByZikulaId($userEntity->getUid());
            if ($mapping) {
                $setPass = null === $mapping->getPass() || '' == $mapping->getPass();
            }
        }
        $form = $this->createForm('Zikula\ZAuthModule\Form\Type\VerifyRegistrationType',
            [
                'uname' => $uname,
                'verifycode' => $verifycode
            ],
            [
                'translator' => $this->getTranslator(),
                'setpass' => $setPass
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userEntity = $this->get('zikula_users_module.user_repository')->findOneBy(['uname' => $data['uname']]);
            $mapping = $this->get('zikula_zauth_module.authentication_mapping_repository')->getByZikulaId($userEntity->getUid());
            if (isset($data['pass'])) {
                $mapping->setPass($this->get('zikula_zauth_module.api.password')->getHashedPassword($data['pass']));
            }
            $mapping->setVerifiedEmail(true);
            $this->get('zikula_zauth_module.authentication_mapping_repository')->persistAndFlush($mapping);
            $this->get('zikula_users_module.helper.registration_helper')->registerNewUser($userEntity);
            $this->get('zikula_zauth_module.user_verification_repository')->resetVerifyChgFor($userEntity->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL);
            $adminNotificationEmail = $this->get('zikula_extensions_module.api.variable')->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');

            switch ($userEntity->getActivated()) {
                case UsersConstant::ACTIVATED_PENDING_REG:
                    $notificationErrors = $this->get('zikula_users_module.helper.mail_helper')->createAndSendRegistrationMail($userEntity, true, !empty($adminNotificationEmail));
                    if (!empty($notificationErrors)) {
                        $this->addFlash('error', implode('<br>', $notificationErrors));
                    }
                    if ('' == $userEntity->getApproved_By()) {
                        $this->addFlash('status', $this->__('Done! Your account has been verified, and is awaiting administrator approval.'));
                    } else {
                        $this->addFlash('status', $this->__('Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.'));
                    }
                    break;
                case UsersConstant::ACTIVATED_ACTIVE:
                    $notificationErrors = $this->get('zikula_users_module.helper.mail_helper')->createAndSendUserMail($userEntity, true, !empty($adminNotificationEmail));
                    if (!empty($notificationErrors)) {
                        $this->addFlash('error', implode('<br>', $notificationErrors));
                    }
                    $this->get('zikula_users_module.helper.access_helper')->login($userEntity);
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
