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

use UserUtil;
use SecurityUtil;
use ModUtil;
use FileUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @deprecated
 * @Route("/admin")
 *
 * Administrator-initiated actions for the Users module.
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::listAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_list'));
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
     * @return RedirectResponse
     */
    public function lostUsernameAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::sendUserNameAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_sendusername', ['user' => $request->get('userid')]));
    }

    /**
     * @Route("/lostpassword/{userid}", requirements={"userid" = "^[1-9]\d*$"})
     * @return RedirectResponse
     */
    public function lostPasswordAction(Request $request, $userid)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::sendConfirmation', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_sendconfirmation', ['user' => $userid]));
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
     * @return RedirectResponse
     */
    public function displayRegistrationAction(Request $request, $uid)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::displayAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_display', ['user' => $uid]));
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
     * @return RedirectResponse
     */
    public function verifyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::verifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_verify', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/approveregistration")
     * @return RedirectResponse
     */
    public function approveRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::approveAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_approve', [
            'user' => $request->get('uid', null),
            'force' => $request->get('force', false)
        ]));
    }

    /**
     * @Route("/denyregistration")
     * @return RedirectResponse
     */
    public function denyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::denyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_deny', ['user' => $request->get('uid', null)]));
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
     * @return RedirectResponse
     */
    public function importAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use FileIOController::importAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_fileio_import'));
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
     * @Route("/forcepasswordchange")
     * @return RedirectResponse
     */
    public function toggleForcedPasswordChangeAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::togglePasswordChangeAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_togglepasswordchange', ['user' => $request->get('userid')]));
    }
}
