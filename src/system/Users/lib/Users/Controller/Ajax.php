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
class Users_Controller_Ajax extends Zikula_Controller
{
    /**
     * Post Setup hook.
     *
     * @return void
     */
    protected function _postSetup()
    {
        parent::_postSetup();

        // Set caching to false by default.
        $this->view->setCaching(false);
    }

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

        $fragment = FormUtil::getPassedValue('fragment');

        ModUtil::dbInfoLoad('Users');
        $tables = DBUtil::getTables();

        $usersColumn = $tables['users_column'];

        $where = 'WHERE ' . $usersColumn['uname'] . ' REGEXP \'(' . DataUtil::formatForStore($fragment) . ')\'';
        $results = DBUtil::selectObjectArray('users', $where);

        // TODO - This should really be in a template.
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
    public function getRegistrationErrors()
    {
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        if (!$this->getVar('reg_allowreg', true)) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Sorry! New user registration is currently disabled.')));
        }

        $reginfo            = DataUtil::convertFromUTF8(FormUtil::getPassedValue('reginfo', null, 'POST'));
        $checkMode          = DataUtil::convertFromUTF8(FormUtil::getPassedValue('checkmode', 'new', 'POST'));
        $userAgreesToTOUPP  = DataUtil::convertFromUTF8(FormUtil::getPassedValue('agreetoterms', false, 'POST'));
        $emailAgain         = DataUtil::convertFromUTF8(FormUtil::getPassedValue('emailagain', '', 'POST'));
        $setPassword        = DataUtil::convertFromUTF8(FormUtil::getPassedValue('setpass', false, 'POST'));
        $passwordAgain      = DataUtil::convertFromUTF8(FormUtil::getPassedValue('passagain', '', 'POST'));
        $antiSpamUserAnswer = DataUtil::convertFromUTF8(FormUtil::getPassedValue('antispamanswer', '', 'POST'));
        $reginfo['dynadata']= DataUtil::convertFromUTF8(FormUtil::getPassedValue('dynadata', array(), 'POST'));

        // Notice: profile fields are checked inside registrationErrors
        $registrationErrors = ModUtil::apiFunc('Users', 'registration', 'getRegistrationErrors', array(
            'checkmode'         => $checkMode,
            'reginfo'           => $reginfo,
            'agreetoterms'      => $userAgreesToTOUPP,
            'setpass'           => $setPassword,
            'passagain'         => $passwordAgain,
            'emailagain'        => $emailAgain,
            'antispamanswer'    => $antiSpamUserAnswer
        ));

        if ($registrationErrors) {
            $errorMessages = array();
            $errorFields = array();
            foreach ($registrationErrors as $field => $messageList) {
                if ($field == 'reginfo_dynadata') {
                    foreach ($messageList['fields'] as $propField) {
                        $errorFields[] = 'prop_' . $propField;
                    }
                    $errorMessages[] = $messageList['result'];
                } else {
                    $errorFields[] = 'users_' . $field;
                    $errorMessages = array_merge($errorMessages, $messageList);
                }
            }
            $returnValue = array(
                'fields'    => $errorFields,
                'messages'  => $errorMessages,
            );
            AjaxUtil::output($returnValue, true, true);
        } else {
            $returnValue = array(
                'fields'    => array(),
                'messages'  => array(),
            );
            AjaxUtil::output($returnValue, true, true);
        }
    }
}
