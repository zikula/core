<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserVerificationEntity;

class RegistrationVerificationHelper
{
    use TranslatorTrait;
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var PermissionApi
     */
    private $permissionApi;
    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;
    /**
     * @var RegistrationHelper
     */
    private $registrationHelper;

    /**
     * RegistrationHelper constructor.
     * @param VariableApi $variableApi
     * @param SessionInterface $session
     * @param PermissionApi $permissionApi
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param TranslatorInterface $translator
     * @param RegistrationHelper $registrationHelper
     */
    public function __construct(
        VariableApi $variableApi,
        SessionInterface $session,
        PermissionApi $permissionApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        TranslatorInterface $translator,
        RegistrationHelper $registrationHelper
    ) {
        $this->variableApi = $variableApi;
        $this->session = $session;
        $this->permissionApi = $permissionApi;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->setTranslator($translator);
        $this->registrationHelper = $registrationHelper;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @param array $reginfo An array containing a valid registration record; optional; if not set, then $uid must
     *                                be set and point to a valid registration record.
     * @param int $uid The uid of a valid registration record; optional; if not set, then $reginfo must be set and valid.
     * @param bool $force Indicates that a verification code should be sent, even if the Users module configuration is
     *                                set not to verify e-mail addresses; optional; only has an effect if the current user is
     *                                an administrator.
     * @param array $rendererArgs Optional arguments to send to the Zikula_View instance while rendering the e-mail message.
     * @return bool True on success; otherwise false.
     */
    public function sendVerificationCode(array $reginfo = null, $uid = null, $force = null, array $rendererArgs = [])
    {
        // In the future, it is possible we will add a feature to allow a newly registered user to resend
        // a new verification code to himself after doing a login-like process with information from  his
        // registration record, so allow not-logged-in plus READ, as well as moderator.

        // we do not check permissions for guests here (see #1874)
        if ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        if (isset($reginfo)) {
            // Got a full reginfo record
            if (!is_array($reginfo)) {
                throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
            }
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
                throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
            }
        } elseif (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
        } else {
            // Got just a uid.
            $reginfo = \UserUtil::getVars($uid, false, 'uid', true);
            if (!$reginfo || empty($reginfo)) {
                throw new \RuntimeException($this->translator->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
            }
            if (!isset($reginfo['email'])) {
                throw new \InvalidArgumentException($this->translator->__f('Error! The registration record with uid \'%1$s\' does not contain an e-mail address.', $uid));
            }
        }

        if ($this->currentUserIsAdmin() && isset($force) && $force) {
            $forceVerification = true;
        } else {
            $forceVerification = false;
        }

        $approvalOrder = $this->variableApi->get('ZikulaUsersModule', 'moderation_order', UsersConstant::APPROVAL_BEFORE);

        // Set the verification code
        if (isset($reginfo['isverified']) && $reginfo['isverified']) {
            throw new \InvalidArgumentException($this->translator->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It is already verified.', $reginfo['uname']));
        } elseif (!$forceVerification && ($approvalOrder == UsersConstant::APPROVAL_BEFORE) && isset($reginfo['approvedby']) && !empty($reginfo['approved_by'])) {
            throw new \InvalidArgumentException($this->translator->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It must first be approved.', $reginfo['uname']));
        }

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $verificationCode = \UserUtil::generatePassword();

        \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'resetVerifyChgFor', [
            'uid' => $reginfo['uid'],
            'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL,
        ]);

        $verifyChgObj = new UserVerificationEntity();
        $verifyChgObj['changetype'] = UsersConstant::VERIFYCHGTYPE_REGEMAIL;
        $verifyChgObj['uid'] = $reginfo['uid'];
        $verifyChgObj['newemail'] = $reginfo['email'];
        $verifyChgObj['verifycode'] = \UserUtil::getHashedPassword($verificationCode);
        $verifyChgObj['created_dt'] = $nowUTC->format(UsersConstant::DATETIME_FORMAT);
        $this->userVerificationRepository->persistAndFlush($verifyChgObj);

        if (empty($rendererArgs)) {
            $siteurl = \System::getBaseUrl();

            $rendererArgs = [];
            $rendererArgs['sitename'] = \System::getVar('sitename');
            $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl) - 1);
        }
        $rendererArgs['reginfo'] = $reginfo;
        $rendererArgs['verifycode'] = $verificationCode;
        $rendererArgs['approvalorder'] = $approvalOrder;

        $codeSent = \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'sendNotification', [
            'toAddress' => $reginfo['email'],
            'notificationType' => 'regverifyemail',
            'templateArgs' => $rendererArgs,
        ]);

        if ($codeSent) {
            return $verifyChgObj['created_dt'];
        } else {
            $this->userVerificationRepository->removeAndFlush($verifyChgObj);

            return false;
        }
    }

    /**
     * Retrieves a verification code for a registration pending e-mail address verification.
     *
     * @param int $uid The uid of the registration for which the code should be retrieved.
     * @return array|bool An array containing the object from the users_verifychg table; an empty array if not found;
     *                      false on error.
     */
    public function getVerificationCode($uid)
    {
        // we do not check permissions for guests here (see #1874)
        if ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
        }

        $verifyChg = $this->userVerificationRepository->findOneBy(['uid' => $uid, 'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL]);

        return $verifyChg;
    }

    /**
     * Processes the results of a registration e-mail verification.
     *
     * If the registration is also approved (or does not need it) a users table record is created.
     *
     * @param array $reginfo
     * @param $uid
     * @return bool True on success; otherwise false.
     */
    public function verify(array $reginfo, $uid)
    {
        if (isset($reginfo)) {
            // Got a full reginfo record
            if (!is_array($reginfo)) {
                throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
            }
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
                throw new \InvalidArgumentException($this->translator->__('Error! Invalid registration record.'));
            }
        } elseif (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
        } else {
            // Got just a uid.
            $reginfo = \UserUtil::getVars($uid, false, 'uid', true);
            if (!$reginfo || empty($reginfo)) {
                throw new \RuntimeException($this->translator->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
            }
            if (!isset($reginfo['email'])) {
                throw new \InvalidArgumentException($this->translator->__f('Error! The registration record with uid \'%1$s\' does not contain an e-mail address.', $uid));
            }
        }

        \UserUtil::setVar('_Users_isVerified', 1, $reginfo['uid']);

        \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'resetVerifyChgFor', [
            'uid' => $reginfo['uid'],
            'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL,
        ]);

        $reginfo = \UserUtil::getVars($reginfo['uid'], true, 'uid', true);

        if (!empty($reginfo['approved_by'])) {
            // The registration is now both verified and approved, time to make an honest user out of him.
            $reginfo = $this->registrationHelper->createUser($reginfo, true, false);
        }

        return $reginfo;
    }

    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return (bool)$this->session->get('uid') && $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN);
    }
}
