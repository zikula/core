<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

/**
 * Controllers provide users access to actions that they can perform on the system;
 * this class provides access to actions initiated through AJAX for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Ajax extends AbstractController
{
    /**
     * Performs a user search based on the user name fragment entered so far.
     *
     * Sends output directly via echo.
     *
     * Available Request Parameters:
     * - fragment (string) A partial user name entered by the user.
     *
     * @return boolean True.
     */
    public function getUsers()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return true;
        }

        $fragment = FormUtil::getpassedValue('fragment');

        ModUtil::dbInfoLoad('Users');
        $pntable = System::dbGetTables();

        $userscolumn = $pntable['users_column'];

        $where = 'WHERE ' . $userscolumn['uname'] . ' REGEXP \'(' . DataUtil::formatForStore($fragment) . ')\'';
        $results = DBUtil::selectObjectArray('users', $where);

        $out = '<ul>';
        if (is_array($results) && count($results) > 0) {
            foreach ($results as $result) {
                $out .= '<li>' . DataUtil::formatForDisplay($result['uname']) .'<input type="hidden" id="'
                     . DataUtil::formatForDisplay($result['uname']) . '" value="' . $result['uid'] . '" /></li>';
            }
        }
        $out .= '</ul>';
        echo DataUtil::convertToUTF8($out);
        return true;
    }

    /**
     * Validate new user information entered by the user.
     *
     * Available Post Parameters:
     * - authid       (string) The system authid used to prevent XSS.
     * - uname        (string) The proposed user name for the new user record.
     * - email        (string) The proposed e-mail address for the new user record.
     * - vemail       (string) A verification of the proposed e-mail address for the new user record.
     * - dynadata     (array)  An array containing data to be stored by the designated profile module and associated with the new account.
     * - agreetoterms (int)    A flag indicating that the user has agreed to the site's terms and policies; 0 indicates no, otherwise yes.
     * - pass         (string) The proposed password for the new user record.
     * - vpass        (string) A verification of the proposed password for the new user record.
     * - reg_answer   (string) The user-entered answer to the registration question.
     *
     * @return array An array containing an error code and a result message. Possible error codes are:
     *               -1=NoPermission 1=EverythingOK 2=NotaValidatedEmailAddr
     *               3=NotAgreeToTerms 4=InValidatedUserName 5=UserNameTooLong
     *               6=UserNameReserved 7=UserNameIncludeSpace 8=UserNameTaken
     *               9=EmailTaken 10=emails different 11=User Agent Banned
     *               12=Email Domain banned 13=DUD incorrect 14=spam question incorrect
     *               15=Pass too short 16=Pass different 17=No pass 18=no password reminder
     */
    public function checkUser()
    {
        if (!SecurityUtil::confirmAuthKey()) {
            AjaxUtil::error(FormUtil::getPassedValue('authid') . ' : ' . $this->__("Sorry! Invalid authorisation key ('authkey'). "
                . "This is probably either because you pressed the 'Back' button to return to a page which does not allow that, "
                . "or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
        }

        $modvars = ModUtil::getVar('Users');

        if (!$modvars['reg_allowreg']) {
            AjaxUtil::error($this->__('Sorry! New user registration is currently disabled.'));
        }

        $uname        = DataUtil::convertFromUTF8(FormUtil::getPassedValue('uname',  null,     'POST'));
        $email        = DataUtil::convertFromUTF8(FormUtil::getPassedValue('email',  null,     'POST'));
        $vemail       = DataUtil::convertFromUTF8(FormUtil::getPassedValue('vemail', null,     'POST'));
        $agreetoterms = DataUtil::convertFromUTF8(FormUtil::getPassedValue('agreetoterms', 0,  'POST'));
        $dynadata     = DataUtil::convertFromUTF8(FormUtil::getPassedValue('dynadata', null,   'POST'));
        $pass         = DataUtil::convertFromUTF8(FormUtil::getPassedValue('pass', null,       'POST'));
        $vpass        = DataUtil::convertFromUTF8(FormUtil::getPassedValue('vpass', null,      'POST'));
        $passwordReminder = DataUtil::convertFromUTF8(FormUtil::getPassedValue('password_reminder', null, 'POST'));
        $reg_answer   = DataUtil::convertFromUTF8(FormUtil::getPassedValue('reg_answer', null, 'POST'));

        if ((!$uname) || !(!preg_match("/[[:space:]]/", $uname)) || !System::varValidate($uname, 'uname')) {
            return array('result' => $this->__('Sorry! The user name you entered is not acceptable. Please correct your entry and try again.'), 'errorcode' => 4);
        }

        if (strlen($uname) > 25) {
            return array('result' => $this->__('Sorry! The user name you entered is too long. The maximum length is 25 characters.'), 'errorcode' => 5);
        }

        $reg_illegalusername = $modvars['reg_Illegalusername'];
        if (!empty($reg_illegalusername)) {
            $usernames = explode(' ', $reg_illegalusername);
            $count = count($usernames);
            $pregcondition = '/((';
            for ($i = 0; $i < $count; $i++) {
                if ($i != $count-1) {
                    $pregcondition .= $usernames[$i] . ')|(';
                } else {
                    $pregcondition .= $usernames[$i] . '))/iAD';
                }
            }
            if (preg_match($pregcondition, $uname)) {
                return array('result' => $this->__('Sorry! The user name you entered is a reserved name.'), 'errorcode' => 6);
            }
        }

        if (strrpos($uname, ' ') > 0) {
            return array('result' => $this->__('Sorry! A user name cannot contain any space characters.'), 'errorcode' => 7);
        }

        // check existing user
        $ucount = DBUtil::selectObjectCountByID ('users', $uname, 'uname', 'lower');
        if ($ucount) {
            return array('result' => $this->__('Sorry! The user name you entered has already been registered.'), 'errorcode' => 8);
        }

        // check pending user
        $ucount = DBUtil::selectObjectCountByID ('users_temp', $uname, 'uname', 'lower');
        if ($ucount) {
            return array('result' => $this->__('Sorry! The user name you entered has already been registered.'), 'errorcode' => 8);
        }

        if (!System::varValidate($email, 'email')) {
            return array('result' => $this->__('Sorry! The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons. '
                . 'Please correct your entry and try again.'), 'errorcode' => 2);
        }

        if ($modvars['reg_uniemail']) {
            $ucount = DBUtil::selectObjectCountByID ('users', $email, 'email');
            if ($ucount) {
                return array('result' => $this->__('Sorry! The e-mail address you entered has already been registered.'), 'errorcode' => 9);
            }
        }

        if ($modvars['moderation']) {
            $ucount = DBUtil::selectObjectCountByID ('users_temp', $uname, 'uname');
            if ($ucount) {
                return array('result' => $this->__('Sorry! The user name you entered has already been registered.'), 'errorcode' => 8);
            }

            $ucount = DBUtil::selectObjectCountByID ('users_temp', $email, 'email');
            if (ModUtil::getVar('Users', 'reg_uniemail')) {
                if ($ucount) {
                    return array('result' => $this->__('Sorry! The e-mail address you entered has already been registered.'), 'errorcode' => 9);
                }
            }
        }

        if ($email !== $vemail) {
            return array('result' => $this->__('Sorry! You did not enter the same e-mail address in each box. Please correct your entry and try again.'), 'errorcode' => 10);
        }

        if (!$modvars['reg_verifyemail'] || $modvars['reg_verifyemail'] == 2) {
            if ((isset($pass)) && ("$pass" != "$vpass")) {
                return array('result' => $this->__('Error! You did not enter the same password in each password field. '
                    . 'Please enter the same password once in each password field (this is required for verification).'),
                    'errorcode' => 16);
            } elseif (isset($pass) && (strlen($pass) < $modvars['minpass'])) {
                return array('result' => $this->_fn('Your password must be at least %s character long', 'Your password must be at least %s characters long',
                    $modvars['minpass'], $modvars['minpass']), 'errorcode' => 15);
            } elseif (empty($pass) && !$modvars['reg_verifyemail']) {
                return array('result' => $this->__('Error! Please enter a password.'), 'errorcode' => 17);
            }
        }

        if (!isset($passwordReminder) || empty($passwordReminder)) {
            return array('result' => $this->__('Error! Please enter a password reminder.'), 'errorcode' => 18);
        }

        if (ModUtil::available('legal')) {
            $tou_active = ModUtil::getVar('legal', 'termsofuse', true);
            $pp_active  = ModUtil::getVar('legal', 'privacypolicy', true);
            if ($tou_active == true && $pp_active == true && $agreetoterms == 0) {
                return array('result' => $this->__('Error! Please click on the checkbox to accept the site\'s \'Terms of use\' and \'Privacy policy\'.'), 'errorcode' => 3);
            }
            if ($tou_active == true && $pp_active == false && $agreetoterms == 0) {
                return array('result' => $this->__('Please click on the checkbox to accept the site\'s \'Terms of use\'.'), 'errorcode' => 3);
            }
            if ($tou_active == false && $pp_active == true && $agreetoterms == 0) {
                return array('result' => $this->__('Please click on the checkbox to accept the site\'s \'Privacy policy\'.'), 'errorcode' => 3);
            }
        }

        $useragent = strtolower(System::serverGetVar('HTTP_USER_AGENT'));
        $illegaluseragents = $modvars['reg_Illegaluseragents'];
        if (!empty($illegaluseragents)) {
            $disallowed_useragents = str_replace(', ', ',', $illegaluseragents);
            $checkdisallowed_useragents = explode(',', $disallowed_useragents);
            $count = count($checkdisallowed_useragents);
            $pregcondition = '/((';
            for ($i = 0; $i < $count; $i++) {
                if ($i != $count-1) {
                    $pregcondition .= $checkdisallowed_useragents[$i] . ')|(';
                } else {
                    $pregcondition .= $checkdisallowed_useragents[$i] . '))/iAD';
                }
            }
            if (preg_match($pregcondition, $useragent)) {
                return array('result' => $this->__('Sorry! The user agent specified is banned.'), 'errorcode' => 11);
            }
        }

        $illegaldomains = $modvars['reg_Illegaldomains'];
        if (!empty($illegaldomains)) {
            list($foo, $maildomain) = explode('@', $email);
            $maildomain = strtolower($maildomain);
            $disallowed_domains = str_replace(', ', ',', $illegaldomains);
            $checkdisallowed_domains = explode(',', $disallowed_domains);
            if (in_array($maildomain, $checkdisallowed_domains)) {
                return array('result' => $this->__('Sorry! E-mail addresses from the domain you entered are not accepted for registering an account on this site.'), 'errorcode' => 12);
            }
        }

        if (!empty($dynadata) && is_array($dynadata)) {
            $required = $this->checkRequired($dynadata);
            if (is_array($required) && !empty($required)) {
                return $required;
            }
        }

        if ($modvars['reg_question'] != '' && $modvars['reg_answer'] != '') {
            if ($reg_answer != $modvars['reg_answer']) {
                return array('result' => $this->__('Sorry! You gave the wrong answer to the anti-spam registration question. Please correct your entry and try again.'), 'errorcode' => 14);
            }
        }

        return array('result' => $this->__("Your entries seem to be OK. Please click on 'Submit registration' when you are ready to continue."), 'errorcode' => 1);
    }

    /**
     * Validates dynamic user data stored by the designated profile module.
     *
     * Internal use only. Not intended to be called through the API.
     *
     * @param array $dynadata An array of data to be validated by the designated profile module.
     *
     * @access private
     *
     * @return array|bool False if there is no dynamic data, if there is no designated profile module, or if there are no errors;
     *                    otherwise an array containg a result message, an error code, and an array of fields containing errors.
     */
    public function checkRequired($dynadata = array())
    {
        if (empty($dynadata)) {
            return false;
        }

        $profileModule = System::getVar('profilemodule', '');
        if (empty($profileModule) || !ModUtil::available($profileModule)) {
            return false;
        }

        // Delegate check to the right module
        $result = ModUtil::apiFunc($profileModule, 'user', 'checkrequired');

        // False: no errors
        if ($result === false) {
            return $result;
        }

        return array('result' => $this->_f('Error! One or more required fields were left blank or incomplete (%s).', $result['translatedFieldsStr']),
                     'errorcode' => 25,
                     'fields' => $result['fields']);
    }
}
