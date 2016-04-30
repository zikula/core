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

use Zikula_View;
use UserUtil;
use SecurityUtil;
use ModUtil;
use Zikula\UsersModule\Constant as UsersConstant;
use DataUtil;
use DateUtil;
use System;
use LogUtil;
use DateTimeZone;
use DateTime;
use FileUtil;
use Exception;
use Zikula_Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/admin")
 *
 * Administrator-initiated actions for the Users module.
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * Run after construction.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Disable caching by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN);
    }

    /**
     * @Route("")
     *
     * Redirects users to the "view" page.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/view")
     * @return RedirectResponse
     */
    public function viewAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::listAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_list'));
    }

    /**
     * @Route("/newuser")
     * @return RedirectResponse
     */
    public function newUserAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::createAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_create'));
    }

    /**
     * @Route("/legacy-search")
     * @return RedirectResponse
     */
    public function searchAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::searchAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_search'));
    }

    /**
     * @Route("/mailusers")
     * @return RedirectResponse
     */
    public function mailUsersAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::searchAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_search'));
    }

    /**
     * @Route("/modify")
     * @return RedirectResponse
     */
    public function modifyAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::modifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_modify', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/lostusername")
     * @Method({"GET", "POST"})
     *
     * Allows an administrator to send a user his user name via email.
     *
     * @param Request $request
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The user id of the user to be modified.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric userid The user id of the user to be modified.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the current user does not have moderate access.
     * @throws \InvalidArgumentException Thrown if the provided user id isn't an integer
     * @throws NotFoundHttpException Thrown if user id doesn't match a valid user
     *
     * @todo The link on the view page should be a mini form, and should post.
     * @todo This should have a confirmation page.
     */
    public function lostUsernameAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $this->checkCsrfToken();
            $uid = $request->request->get('userid', null);
        } else {
            $this->checkCsrfToken($request->query->get('csrftoken'));
            $uid = $request->query->get('userid', null);
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            throw new NotFoundHttpException($this->__('Sorry! Unable to retrieve information for that user id.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $userNameSent = ModUtil::apiFunc($this->name, 'user', 'mailUname', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($userNameSent) {
            $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The user name for \'%s\' has been sent via e-mail.', $user['uname']));
        } elseif (!$request->getSession()->getFlashBag()->has(Zikula_Session::MESSAGE_ERROR)) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Sorry! There was an unknown error while trying to send the user name for \'%s\'.', $user['uname']));
        }

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/lostpassword/{userid}", requirements={"userid" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Allows an administrator to send a user a password recovery verification code.
     *
     * @param Request $request
     * @param integer $userid
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the current user does not have moderate access.
     * @throws \InvalidArgumentException Thrown if the provided user id isn't an integer or
     *                                          if the user has no password set
     * @throws NotFoundHttpException Thrown if user id doesn't match a valid user
     *
     * @todo The link on the view page should be a mini form, and should post.
     * @todo This should have a confirmation page.
     */
    public function lostPasswordAction(Request $request, $userid)
    {
        $this->checkCsrfToken($request->query->get('csrftoken'));

        $user = UserUtil::getVars($userid);
        if (!$user) {
            throw new NotFoundHttpException($this->__('Sorry! Unable to retrieve information for that user id.'));
        }

        if ($user['pass'] == UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
            // User has no password set -> Sending a recovery code is useless.
            throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
        }

        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $confirmationCodeSent = ModUtil::apiFunc($this->name, 'user', 'mailConfirmationCode', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($confirmationCodeSent) {
            $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The password recovery verification code for %s has been sent via e-mail.', $user['uname']));
        }

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/deleteusers")
     * @return RedirectResponse
     */
    public function deleteUsersAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::deleteAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_delete'));
    }

    /**
     * @Route("/viewregistrations")
     * @return RedirectResponse
     */
    public function viewRegistrationsAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::listAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_list'));
    }

    /**
     * @Route("/displayregistration/{uid}", requirements={"uid" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Displays the information on a single registration request.
     *
     * @param Request $request
     * @param integer $uid The id of the registration request (id) to retrieve and display.
     *
     * @return Response symfony response object containing the rendered template.
     *
     * @throws AccessDeniedException Thrown if the current user does not have moderate access
     * @throws FatalErrorException Thrown if the method of accessing this function is improper
     * @throws \InvalidArgumentException Thrown if the user id isn't set or numeric
     */
    public function displayRegistrationAction(Request $request, $uid)
    {
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

//        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        $reginfo = $this->get('zikulausersmodule.helper.registration_helper')->get($uid);
        if (!$reginfo) {
            // get application could fail (return false) because of a nonexistant
            // record, no permission to read an existing record, or a database error
            $request->getSession()->getFlashBag()->add('error', $this->__('Unable to retrieve registration record. The record with the specified id might not exist, or you might not have permission to access that record.'));

            return false;
        }

        // So expiration can be displayed
        $regExpireDays = $this->getVar('reg_expiredays', 0);
        if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
            try {
                $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                $expiresUTC = new DateTime(UsersConstant::EXPIRED, new DateTimeZone('UTC'));
            }
            $expiresUTC->modify("+{$regExpireDays} days");
            $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UsersConstant::DATETIME_FORMAT),
                $this->__('%m-%d-%Y %H:%M'));
        }

        $actions = $this->getActionsForRegistrations(array($reginfo), 'display');

        return new Response($this->view->assign('reginfo', $reginfo)
            ->assign('actions', $actions)
            ->fetch('Admin/displayregistration.tpl'));
    }

    /**
     * @Route("/modifyregistration")
     * @return RedirectResponse
     */
    public function modifyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::modifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_modify', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/verifyregistration")
     * @Method({"GET", "POST"})
     *
     * Renders and processes the admin's force-verify form.
     *
     * Renders and processes a form confirming an administrators desire to skip verification for
     * a registration record, approve it and add it to the users table.
     *
     * @param Request $request
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to verify.
     * boolean force      True to force the registration to be verified.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric uid        The id of the registration request (uid) to verify.
     * boolean force      True to force the registration to be verified.
     * boolean confirmed  True to execute this function's action.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the current user does not have moderate access
     * @throws FatalErrorException Thrown if the method of accessing this function is improper
     * @throws \InvalidArgumentException Thrown if the user id isn't set or numeric
     * @throws NotFoundHttpException Thrown if the registration record couldn't be retrieved
     */
    public function verifyRegistrationAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() == 'GET') {
            $uid = $request->query->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $request->query->get('force', false);
            $restoreView = $request->query->get('restoreview', 'view');
            $confirmed = false;
        } elseif ($request->getMethod() == 'POST') {
            $this->checkCsrfToken();
            $uid = $request->request->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $request->request->get('force', false);
            $restoreView = $request->request->get('restoreview', 'view');
            $confirmed = $request->request->get('confirmed', false);
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
        }

        // Got just a uid.
//        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        $reginfo = $this->get('zikulausersmodule.helper.registration_helper')->get($uid);
        if (!$reginfo) {
            throw new NotFoundHttpException($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
        }

        if ($restoreView == 'display') {
            $cancelUrl = $this->get('router')->generate('zikulausersmodule_admin_displayregistration', array('uid' => $reginfo['uid']), RouterInterface::ABSOLUTE_URL);
        } else {
            $cancelUrl = $this->get('router')->generate('zikulausersmodule_admin_viewregistrations', array('restoreview' => true), RouterInterface::ABSOLUTE_URL);
        }

        $approvalOrder = $this->getVar('moderation_order', UsersConstant::APPROVAL_BEFORE);

        if ($reginfo['isverified']) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It is already verified.', $reginfo['uname']));
        } elseif (!$forceVerification && ($approvalOrder == UsersConstant::APPROVAL_BEFORE) && !$reginfo['isapproved']) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It must first be approved.', $reginfo['uname']));
        }

        if (!$confirmed) {
            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && $reginfo['verificationsent'] && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(UsersConstant::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UsersConstant::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            return new Response($this->view->assign('reginfo', $reginfo)
                              ->assign('restoreview', $restoreView)
                              ->assign('force', $forceVerification)
                              ->assign('cancelurl', $cancelUrl)
                              ->fetch('Admin/verifyregistration.tpl'));
        } else {
//            $verificationSent = ModUtil::apiFunc($this->name, 'registration', 'sendVerificationCode', array(
//                'reginfo'   => $reginfo,
//                'force'     => $forceVerification,
//            ));
            $verificationSent = $this->get('zikulausersmodule.helper.registration_verification_helper')->sendVerificationCode(null, $uid, true);

            if (!$verificationSent) {
                $request->getSession()->getFlashBag()->add('error', $this->__f('Sorry! There was a problem sending a verification code to \'%1$s\'.', $reginfo['uname']));
            } else {
                $request->getSession()->getFlashBag()->add('status', $this->__f('Done! Verification code sent to \'%1$s\'.', $reginfo['uname']));
            }

            return new RedirectResponse($cancelUrl);
        }
    }

    /**
     * @Route("/approveregistration")
     * @Method({"GET", "POST"})
     *
     * Renders and processes a form confirming an administrators desire to approve a registration.
     *
     * @param Request $request
     *
     * If the registration record is also verified (or verification is not needed) a users table
     * record is created.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to approve.
     * boolean force      True to force the registration to be approved.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric uid        The id of the registration request (uid) to approve.
     * boolean force      True to force the registration to be approved.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * @return Response Symfony response object
     *
     * @throws AccessDeniedException Thrown if the current user does not have moderate access
     * @throws FatalErrorException Thrown if the method of accessing this function is improper
     * @throws \InvalidArgumentException Thrown if the user id isn't set or numeric
     * @throws NotFoundHttpException Thrown if the registration record couldn't be retrieved
     */
    public function approveRegistrationAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() == 'GET') {
            $uid = $request->query->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $request->query->get('force', false);
            $restoreView = $request->query->get('restoreview', 'view');
        } elseif ($request->getMethod() == 'POST') {
            $uid = $request->request->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $request->request->get('force', false);
            $restoreView = $request->request->get('restoreview', 'view');
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
        }

        // Got just an id.
//        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        $reginfo = $this->get('zikulausersmodule.helper.registration_helper')->get($uid);
        if (!$reginfo) {
            throw new NotFoundHttpException($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
        }

        if ($restoreView == 'display') {
            $cancelUrl = $this->get('router')->generate('zikulausersmodule_admin_displayregistration', array('uid' => $reginfo['uid']), RouterInterface::ABSOLUTE_URL);
        } else {
            $cancelUrl = $this->get('router')->generate('zikulausersmodule_admin_viewregistrations', array('restoreview' => true), RouterInterface::ABSOLUTE_URL);
        }

        $approvalOrder = $this->getVar('moderation_order', UsersConstant::APPROVAL_BEFORE);

        if ($reginfo['isapproved'] && !$forceVerification) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Warning! Nothing to do! The registration record with uid \'%1$s\' is already approved.', $reginfo['uid']));
        } elseif (!$forceVerification && ($approvalOrder == UsersConstant::APPROVAL_AFTER) && !$reginfo['isapproved']
                && !SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The registration record with uid \'%1$s\' cannot be approved. The registration\'s e-mail address must first be verified.', $reginfo['uid']));
        } elseif ($forceVerification && (!isset($reginfo['pass']) || empty($reginfo['pass']))) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! E-mail verification cannot be skipped for \'%1$s\'. The user must establish a password as part of the verification process.', $reginfo['uname']));
        }

        $confirmed = $request->get('confirmed', false);
        if (!$confirmed) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(UsersConstant::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UsersConstant::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            return new Response($this->view->assign('reginfo', $reginfo)
                              ->assign('restoreview', $restoreView)
                              ->assign('force', $forceVerification)
                              ->assign('cancelurl', $cancelUrl)
                              ->fetch('Admin/approveregistration.tpl'));
        } else {
            $this->checkCsrfToken();

//            $approved = ModUtil::apiFunc($this->name, 'registration', 'approve', array(
//                'reginfo'   => $reginfo,
//                'force'     => $forceVerification,
//            ));
            $approved = $this->get('zikulausersmodule.helper.registration_helper')->approve($reginfo, null, $forceVerification);

            if (!$approved) {
                $request->getSession()->getFlashBag()->add('error', $this->__f('Sorry! There was a problem approving the registration for \'%1$s\'.', $reginfo['uname']));
            } else {
                if (isset($approved['uid'])) {
                    $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The registration for \'%1$s\' has been approved and a new user account has been created.', $reginfo['uname']));

                    return new RedirectResponse($cancelUrl);
                } else {
                    $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The registration for \'%1$s\' has been approved and is awaiting e-mail verification.', $reginfo['uname']));

                    return new RedirectResponse($cancelUrl);
                }
            }
        }
    }

    /**
     * @Route("/denyregistration")
     * @Method({"GET", "POST"})
     *
     * Render and process a form confirming the administrator's rejection of a registration.
     *
     * @param Request $request
     *
     * If the denial is confirmed, the registration is deleted from the database.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to deny.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric uid        The id of the registration request (uid) to deny.
     * boolean confirmed  True to execute this function's action.
     * boolean usernorify True to notify the user that his registration request was denied; otherwise false.
     * string  reason     The reason the registration request was denied, included in the notification.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * @return Response Symfony response object
     *
     * @throws AccessDeniedException Thrown if the current user does not have delete access
     * @throws FatalErrorException Thrown if the method of accessing this function is improper
     * @throws \InvalidArgumentException Thrown if the user id isn't set or numeric
     * @throws NotFoundHttpException Thrown if the registration record couldn't be retrieved
     */
    public function denyRegistrationAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() == 'GET') {
            $uid = $request->query->get('uid', null);
            $restoreView = $request->query->get('restoreview', 'view');
            $confirmed = false;
        } elseif ($request->getMethod() == 'POST') {
            $this->checkCsrfToken();
            $uid = $request->request->get('uid', null);
            $restoreView = $request->request->get('restoreview', 'view');
            $sendNotification = $request->request->get('usernotify', false);
            $reason = $request->request->get('reason', '');
            $confirmed = $request->request->get('confirmed', false);
        } else {
            throw new FatalErrorException();
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
        }

        // Got just a uid.
//        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        $reginfo = $this->get('zikulausersmodule.helper.registration_helper')->get($uid);
        if (!$reginfo) {
            throw new NotFoundHttpException($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
        }

        if ($restoreView == 'display') {
            $cancelUrl = $this->get('router')->generate('zikulausersmodule_admin_displayregistration', array('uid' => $reginfo['uid']), RouterInterface::ABSOLUTE_URL);
        } else {
            $cancelUrl = $this->get('router')->generate('zikulausersmodule_admin_viewregistrations', array('restoreview' => true), RouterInterface::ABSOLUTE_URL);
        }

        if (!$confirmed) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(UsersConstant::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UsersConstant::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            return new Response($this->view->assign('reginfo', $reginfo)
                              ->assign('restoreview', $restoreView)
                              ->assign('cancelurl', $cancelUrl)
                              ->fetch('Admin/denyregistration.tpl'));
        } else {
//            $denied = ModUtil::apiFunc($this->name, 'registration', 'remove', array(
//                'reginfo'   => $reginfo,
//            ));
            $denied = $this->get('zikulausersmodule.helper.registration_helper')->remove(null, $reginfo);

            if (!$denied) {
                $request->getSession()->getFlashBag()->add('error', $this->__f('Sorry! There was a problem deleting the registration for \'%1$s\'.', $reginfo['uname']));
            } else {
                if ($sendNotification) {
                    $siteurl   = System::getBaseUrl();
                    $rendererArgs = array(
                        'sitename'  => System::getVar('sitename'),
                        'siteurl'   => substr($siteurl, 0, strlen($siteurl) - 1),
                        'reginfo'   => $reginfo,
                        'reason'    => $reason,
                    );

                    $sent = ModUtil::apiFunc($this->name, 'user', 'sendNotification', array(
                        'toAddress'         => $reginfo['email'],
                        'notificationType'  => 'regdeny',
                        'templateArgs'      => $rendererArgs
                    ));
                }
                $request->getSession()->getFlashBag()->add('status', $this->__f('Done! The registration for \'%1$s\' has been denied and deleted.', $reginfo['uname']));

                return new RedirectResponse($cancelUrl);
            }
        }
    }

    /**
     * @Route("/config")
     * @return RedirectResponse
     */
    public function configAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use ConfigController::configAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_config_config'));
    }

    /**
     * @Route("/import")
     * @Method({"GET", "POST"})
     *
     * Show the form to choose a CSV file and import several users from this file.
     *
     * @param Request $request
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * boolean confirmed  True if the user has confirmed the upload/import.
     * array   importFile Structured information about the file to import, from <input type="file" name="fileFieldName" ... /> and stored
     *                          in $_FILES['fileFieldName']. See http://php.net/manual/en/features.file-upload.post-method.php .
     * integer delimiter  A code indicating the type of delimiter found in the import file. 1 = comma, 2 = semicolon, 3 = colon.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the current user does not have add access.
     */
    public function importAction(Request $request)
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        // get input values. Check for direct function call first because calling function might be either get or post
        if ($request->getMethod() == 'GET') {
            $confirmed = false;
        } elseif ($request->getMethod() == 'POST') {
            $this->checkCsrfToken();
            $confirmed = $request->request->get('confirmed', false);
        }

        // set default parameters
        $minpass = $this->getVar('minpass');
        $defaultGroup = ModUtil::getVar('ZikulaGroupsModule', 'defaultgroup');

        if ($confirmed) {
            // get other import values
            $importFile = $request->files->get('importFile', null);
            $delimiter = $request->request->get('delimiter', null);
            $importResults = $this->uploadImport($importFile, $delimiter);
            if ($importResults == '') {
                // the users have been imported successfully
                $request->getSession()->getFlashBag()->add('status', $this->__('Done! Users imported successfully.'));

                return new RedirectResponse($this->get('router')->generate('zikulausersmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
            }
        }

        // shows the form
        $post_max_size = ini_get('post_max_size');
        // get default group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $defaultGroup));
        $defaultGroup = $defaultGroup . ' (' . $group['name'] . ')';

        return new Response($this->view->assign('importResults', isset($importResults) ? $importResults : '')
                ->assign('post_max_size', $post_max_size)
                ->assign('minpass', $minpass)
                ->assign('defaultGroup', $defaultGroup)
                ->fetch('Admin/import.tpl'));
    }

    /**
     * @Route("/export")
     * @Method({"GET", "POST"})
     *
     * Show the form to export a CSV file of users.
     *
     * @param Request $request
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * boolean confirmed       True if the user has confirmed the export.
     * string  exportFile      Filename of the file to export (optional) (default=users.csv)
     * integer delimiter       A code indicating the type of delimiter found in the export file. 1 = comma, 2 = semicolon, 3 = colon, 4 = tab.
     * integer exportEmail     Flag to export email addresses, 1 for yes.
     * integer exportTitles    Flag to export a title row, 1 for yes.
     * integer exportLastLogin Flag to export the last login date/time, 1 for yes.
     * integer exportRegDate   Flag to export the registration date/time, 1 for yes.
     * integer exportGroups    Flag to export the group membership, 1 for yes.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return Response
     *
     * @throws \InvalidArgumentException Thrown if parameters are passed via the $args array, but $args is invalid.
     * @throws AccessDeniedException Thrown if the current user does not have admin access
     * @throws FatalErrorException Thrown if the method of accessing this function is improper
     */
    public function exporterAction(Request $request)
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() == 'GET') {
            $confirmed = false;
        } elseif ($request->getMethod() == 'POST') {
            $this->checkCsrfToken();
            $confirmed = $request->request->get('confirmed', false);
            $exportFile = $request->request->get('exportFile', null);
            $delimiter = $request->request->get('delimiter', null);
            $email = $request->request->get('exportEmail', null);
            $titles = $request->request->get('exportTitles', null);
            $lastLogin = $request->request->get('exportLastLogin', null);
            $regDate = $request->request->get('exportRegDate', null);
            $groups = $request->request->get('exportGroups', null);
        }

        if ($confirmed) {
            // get other import values
            $email = (!isset($email) || $email !== '1') ? false : true;
            $titles = (!isset($titles) || $titles !== '1') ? false : true;
            $lastLogin = (!isset($lastLogin) || $lastLogin !== '1') ? false : true;
            $regDate = (!isset($regDate) || $regDate !== '1') ? false : true;
            $groups = (!isset($groups) || $groups !== '1') ? false : true;

            if (!isset($delimiter) || $delimiter == '') {
                $delimiter = 1;
            }
            switch ($delimiter) {
                case 1:
                    $delimiter = ",";
                    break;
                case 2:
                    $delimiter = ";";
                    break;
                case 3:
                    $delimiter = ":";
                    break;
                case 4:
                    $delimiter = chr(9);
            }
            if (!isset($exportFile) || $exportFile == '') {
                $exportFile = 'users.csv';
            }
            if (!strrpos($exportFile, '.csv')) {
                $exportFile .= '.csv';
            }

            $colnames = array();

            //get all user fields
            if (ModUtil::available('ProfileModule')) {
                $userfields = ModUtil::apiFunc('ProfileModule', 'user', 'getallactive');

                foreach ($userfields as $item) {
                    $colnames[] = $item['prop_attribute_name'];
                }
            }

            // title fields
            if ($titles == 1) {
                $titlerow = array('id', 'uname');

                //titles for optional data
                if ($email == 1) {
                    array_push($titlerow, 'email');
                }
                if ($regDate == 1) {
                    array_push($titlerow, 'user_regdate');
                }
                if ($lastLogin == 1) {
                    array_push($titlerow, 'lastlogin');
                }
                if ($groups == 1) {
                    array_push($titlerow, 'groups');
                }

                array_merge($titlerow, $colnames);
            } else {
                $titlerow = array();
            }

            //get all users
            $users = ModUtil::apiFunc($this->name, 'user', 'getAll');

            // get all groups
            $allgroups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
            $groupnames = array();
            foreach ($allgroups as $groupitem) {
                $groupnames[$groupitem['gid']] = $groupitem['name'];
            }

            // data for csv
            $datarows = array();

            //loop every user gettin user id and username and all user fields and push onto result array.
            foreach ($users as $user) {
                $uservars = UserUtil::getVars($user['uid']);

                $result = array();

                array_push($result, $uservars['uid'], $uservars['uname']);

                //checks for optional data
                if ($email == 1) {
                    array_push($result, $uservars['email']);
                }
                if ($regDate == 1) {
                    array_push($result, $uservars['user_regdate']);
                }
                if ($lastLogin == 1) {
                    array_push($result, $uservars['lastlogin']);
                }

                if ($groups == 1) {
                    $usergroups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getusergroups',
                                            array('uid'   => $uservars['uid'],
                                                  'clean' => true));

                    $groupstring = "";

                    foreach ($usergroups as $group) {
                        $groupstring .= $groupnames[$group] . chr(124);
                    }

                    $groupstring = rtrim($groupstring, chr(124));

                    array_push($result, $groupstring);
                }

                foreach ($colnames as $colname) {
                    array_push($result, $uservars['__ATTRIBUTES__'][$colname]);
                }

                array_push($datarows, $result);
            }

            //export the csv file
            FileUtil::exportCSV($datarows, $titlerow, $delimiter, '"', $exportFile);
        }

        if (SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            $this->view->assign('groups', '1');
        }

        return new Response($this->view->fetch('Admin/export.tpl'));
    }

    /**
     * Import several users from a CSV file. Checks needed values and format.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param array $importFile Information about the file to import. Used as the default
     *                            if $_FILES['importFile'] is not set. Allows this function to be called internally,
     *                            rather than as a result of a form post.
     * @param integer $delimiter A code indicating the delimiter used in the file. Used as the
     *                            default if $_POST['delimiter'] is not set. Allows this function to be called internally,
     *                            rather than as a result of a form post.
     *
     * @return string an empty message if success or an error message otherwise
     */
    protected function uploadImport(array $importFile, $delimiter)
    {
        // get needed values
        $is_admin = (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) ? true : false;
        $minpass = $this->getVar('minpass');
        $defaultGroup = ModUtil::getVar('ZikulaGroupsModule', 'defaultgroup'); // Create output object;
        // calcs $pregcondition needed to verify illegal usernames
        $reg_illegalusername = $this->getVar('reg_Illegalusername');
        $pregcondition = '';
        if (!empty($reg_illegalusername)) {
            $usernames = explode(" ", $reg_illegalusername);
            $count = count($usernames);
            $pregcondition = "/((";
            for ($i = 0; $i < $count; $i++) {
                if ($i != $count - 1) {
                    $pregcondition .= $usernames[$i] . ")|(";
                } else {
                    $pregcondition .= $usernames[$i] . "))/iAD";
                }
            }
        }

        // get available groups
        $allGroups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');

        // create an array with the groups identities where the user can add other users
        $allGroupsArray = array();
        foreach ($allGroups as $group) {
            if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'] . '::', ACCESS_EDIT)) {
                $allGroupsArray[] = $group['gid'];
            }
        }

        // check if the user's email must be unique
        $reg_uniemail = $this->getVar('reg_uniemail');

        // get the CSV delimiter
        switch ($delimiter) {
            case 1:
                $delimiterChar = ",";
                break;
            case 2:
                $delimiterChar = ";";
                break;
            case 3:
                $delimiterChar = ":";
                break;
        }

        // check that the user have selected a file
        $fileName = $importFile['name'];
        if ($fileName == '') {
            return $this->__("Error! You have not chosen any file.");
        }

        // check if user have selected a correct file
        if (FileUtil::getExtension($fileName) != 'csv') {
            return $this->__("Error! The file extension is incorrect. The only allowed extension is csv.");
        }

        // read the choosen file
        if (!$lines = file($importFile['tmp_name'])) {
            return $this->__("Error! It has not been possible to read the import file.");
        }
        $expectedFields = array('uname', 'pass', 'email', 'activated', 'sendmail', 'groups');
        $counter = 0;
        $importValues = array();
        $usersArray = array();
        $emailsArray = array();

        // read the lines and create an array with the values. Check if the values passed are correct and set the default values if it is necessary
        foreach ($lines as $line_num => $line) {
            $line = str_replace('"', '', trim($line));
            if ($counter == 0) {
                // check the fields defined in the first row
                $firstLineArray = explode($delimiterChar, $line);
                foreach ($firstLineArray as $field) {
                    if (!in_array(trim(strtolower($field)), $expectedFields)) {
                        return $this->__f("Error! The import file does not have the expected field %s in the first row. Please check your import file.", array($field));
                    }
                }
                $counter++;
                continue;
            }
            // get and check the second and following lines
            $lineArray = array();
            $lineArray = DataUtil::formatForOS(explode($delimiterChar, $line));

            // check if the line have all the needed values
            if (count($lineArray) != count($firstLineArray)) {
                return $this->__f('Error! The number of parameters in line %s is not correct. Please check your import file.', $counter);
            }
            $importValues[] = array_combine($firstLineArray, $lineArray);

            // check all the obtained values
            // check user name
            $uname = trim($importValues[$counter - 1]['uname']);
            if ($uname == '' || strlen($uname) > 25) {
                return $this->__f('Sorry! The user name is not valid in line %s. The user name is mandatory and the maximum length is 25 characters. Please check your import file.',
                    $counter);
            }

            // check if it is a valid user name
            // admins are allowed to add any usernames, even those defined as being illegal
            if (!$is_admin && $pregcondition != '') {
                // check for illegal usernames
                if (preg_match($pregcondition, $uname)) {
                    return $this->__f('Sorry! The user name %1$s is reserved and cannot be registered in line %2$s. Please check your import file.', array($uname, $counter));
                }
            }

            // check if the user name is valid because spaces or invalid characters
            if (preg_match("/[[:space:]]/", $uname) || !System::varValidate($uname, 'uname')) {
                return $this->__f('Sorry! The user name %1$s cannot contain spaces in line %2$s. Please check your import file.', array($uname, $counter));
            }

            // check if the user name is repeated
            if (in_array($uname, $usersArray)) {
                return $this->__f('Sorry! The user name %1$s is repeated in line %2$s, and it cannot be used twice for creating accounts. Please check your import file.',
                    array($uname, $counter));
            }
            $usersArray[] = $uname;

            // check password
            $pass = (string)trim($importValues[$counter - 1]['pass']);
            if ($pass == '') {
                return $this->__f('Sorry! You did not provide a password in line %s. Please check your import file.', $counter);
            }

            // check password length
            if (strlen($pass) <  $minpass) {
                return $this->__f('Sorry! The password must be at least %1$s characters long in line %2$s. Please check your import file.', array($minpass, $counter));
            }

            // check email
            $email = trim($importValues[$counter - 1]['email']);
            if ($email == '') {
                return $this->__f('Sorry! You did not provide a email in line %s. Please check your import file.', $counter);
            }

            // check email format
            if (!System::varValidate($email, 'email')) {
                return $this->__f('Sorry! The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons in line %s. Please check your import file.', $counter);
            }

            // check if email is unique only if it is necessary
            if ($reg_uniemail == 1) {
                if (in_array($email, $emailsArray)) {
                    return $this->__f('Sorry! The %1$s e-mail address is repeated in line %2$s, and it cannot be used twice for creating accounts. Please check your import file.',
                        array($email, $counter));
                }
                $emailsArray[] = $email;
            }

            // validate activation value
            $importValues[$counter - 1]['activated'] = isset($importValues[$counter - 1]['activated']) ? (int)$importValues[$counter - 1]['activated'] : UsersConstant::ACTIVATED_ACTIVE;
            $activated = $importValues[$counter - 1]['activated'];
            if (($activated != UsersConstant::ACTIVATED_INACTIVE) && ($activated != UsersConstant::ACTIVATED_ACTIVE)) {
                return $this->__('Error! The CSV is not valid: the "activated" column must contain 0 or 1 only.');
            }

            // validate sendmail
            $importValues[$counter - 1]['sendmail'] = isset($importValues[$counter - 1]['sendmail']) ? (int)$importValues[$counter - 1]['sendmail'] : 0;
            if ($importValues[$counter - 1]['sendmail'] < 0 || $importValues[$counter - 1]['sendmail'] > 1) {
                return $this->__('Error! The CSV is not valid: the "sendmail" column must contain 0 or 1 only.');
            }

            // check groups and set defaultGroup as default if there are not groups defined
            $importValues[$counter - 1]['groups'] = isset($importValues[$counter - 1]['groups']) ? (int)$importValues[$counter - 1]['groups'] : '';
            $groups = $importValues[$counter - 1]['groups'];
            if ($groups == '') {
                $importValues[$counter - 1]['groups'] = $defaultGroup;
            } else {
                $groupsArray = explode('|', $groups);
                foreach ($groupsArray as $group) {
                    if (!in_array($group, $allGroupsArray)) {
                        return $this->__f('Sorry! The identity of the group %1$s is not not valid in line %2$s. Perhaps it do not exist. Please check your import file.', array($group, $counter));
                    }
                }
            }
            $counter++;
        }

        // seams that the import file is formated correctly and its values are valid
        if (empty($importValues)) {
            return $this->__("Error! The import file does not have values.");
        }

        // check if users exists in database
        $usersInDB = ModUtil::apiFunc($this->name, 'admin', 'checkMultipleExistence',
                                      array('valuesarray' => $usersArray,
                                            'key' => 'uname'));
        if ($usersInDB === false) {
            return $this->__("Error! Trying to read the existing user names in database.");
        } else {
            if (count($usersInDB) > 0) {
                return $this->__("Sorry! One or more user names really exist in database. The user names must be uniques.");
            }
        }

        // check if emails exists in data base in case the email have to be unique
        if ($reg_uniemail == 1) {
            $emailsInDB = ModUtil::apiFunc($this->name, 'admin', 'checkMultipleExistence',
                                          array('valuesarray' => $emailsArray,
                                                'key' => 'email'));
            if ($emailsInDB === false) {
                return $this->__("Error! Trying to read the existing users' email addressess in database.");
            } else {
                if (count($emailsInDB) > 0) {
                    return $this->__("Sorry! One or more users' email addresses exist in the database. Each user's e-mail address must be unique.");
                }
            }
        }

        // seems that the values in import file are ready. Procceed creating users
        if (!ModUtil::apiFunc($this->name, 'admin', 'createImport', array('importvalues' => $importValues))) {
            return $this->__("Error! The creation of users has failed.");
        }

        return '';
    }

    /**
     * @Route("/forcepasswordchange")
     * @Method({"GET", "POST"})
     *
     * Sets or resets a user's need to changed his password on his next attempt at logging in.
     *
     * @param Request $request
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The uid of the user for whom a change of password should be forced (or canceled).
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric userid                    The uid of the user for whom a change of password should be forced (or canceled).
     * boolean user_must_change_password True to force the user to change his password at his next log-in attempt, otherwise false.
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if a user id is not specified, is invalid, or does not point to a valid account record,
     *                                      or the account record is not in a consistent state.
     * @throws AccessDeniedException Thrown if the current user does not have edit access for the account record.
     * @throws FatalErrorException Thrown if the method of accessing this function is improper
     */
    public function toggleForcedPasswordChangeAction(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $uid = $request->query->get('userid', false);

            if (!$uid || !is_numeric($uid) || ((int)$uid != $uid)) {
                throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
            }

            $userObj = UserUtil::getVars($uid);

            if (!isset($userObj) || !$userObj || !is_array($userObj) || empty($userObj) || $userObj['pass'] == UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
                throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
            }

            if (!SecurityUtil::checkPermission('ZikulaUsersModule::', "{$userObj['uname']}::{$uid}", ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }

            $userMustChangePassword = UserUtil::getVar('_Users_mustChangePassword', $uid, false);

            return new Response($this->view->assign('user_obj', $userObj)
                ->assign('user_must_change_password', $userMustChangePassword)
                ->fetch('Admin/toggleforcedpasswordchange.tpl'));
        } elseif ($request->getMethod() == 'POST') {
            $this->checkCsrfToken();

            $uid = $request->request->get('userid', false);
            $userMustChangePassword = $request->request->get('user_must_change_password', false);

            // Force reload of User object into cache.
            $userObj = UserUtil::getVars($uid);

            if (!$uid || !is_numeric($uid) || ((int)$uid != $uid) || $userObj['pass'] == UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
                throw new \InvalidArgumentException(LogUtil::getErrorMsgArgs());
            }

            if (!SecurityUtil::checkPermission('ZikulaUsersModule::', "{$userObj['uname']}::{$uid}", ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }

            if ($userMustChangePassword) {
                UserUtil::setVar('_Users_mustChangePassword', $userMustChangePassword, $uid);
            } else {
                UserUtil::delVar('_Users_mustChangePassword', $uid);
            }

            // Force reload of User object into cache.
            $userObj = UserUtil::getVars($uid, true);

            if ($userMustChangePassword) {
                if (isset($userObj['__ATTRIBUTES__']) && isset($userObj['__ATTRIBUTES__']['_Users_mustChangePassword'])) {
                    $request->getSession()->getFlashBag()->add('status', $this->__f('Done! A password change will be required the next time %1$s logs in.', array($userObj['uname'])));
                } else {
                    throw new \InvalidArgumentException();
                }
            } else {
                if (isset($userObj['__ATTRIBUTES__']) && isset($userObj['__ATTRIBUTES__']['_Users_mustChangePassword'])) {
                    throw new \InvalidArgumentException();
                } else {
                    $request->getSession()->getFlashBag()->add('status', $this->__f('Done! A password change will no longer be required for %1$s.', array($userObj['uname'])));
                }
            }

            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
        }
    }
}
