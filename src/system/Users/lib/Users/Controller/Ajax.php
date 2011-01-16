<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Access to actions initiated through AJAX for the Users module.
 */
class Users_Controller_Ajax extends Zikula_Controller
{
    /**
     * Post setup.
     *
     * @return void
     */
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }
    
    /**
     * Performs a user search based on the user name fragment entered so far.
     *
     * Available Request Parameters:
     * - fragment (string) A partial user name entered by the user.
     *
     * @return string Zikula_Response_Ajax_Plain with list of users matching the criteria.
     */
    public function getUsers()
    {
        $view = Zikula_View::getInstance('Users');

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return new Zikula_Response_Ajax_Plain(DataUtil::convertToUTF8($view->fetch('users_ajax_getusers.tpl')));
        }

        $fragment = FormUtil::getPassedValue('fragment');

        ModUtil::dbInfoLoad('Users');
        $tables = DBUtil::getTables();

        $usersColumn = $tables['users_column'];

        $where = 'WHERE ' . $usersColumn['uname'] . ' REGEXP \'(' . DataUtil::formatForStore($fragment) . ')\'';
        $results = DBUtil::selectObjectArray('users', $where);

        $view->assign('results', $results);
        $output = $view->fetch('users_ajax_getusers.tpl');

        return new Zikula_Response_Ajax_Plain(DataUtil::convertToUTF8($output));
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
     * @return array A Zikula_Response_Ajax containing an array of error code and a result message. Possible error codes are:
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
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        if (!$this->getVar('reg_allowreg', true) && !SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Fatal($this->__('Sorry! New user registration is currently disabled.'));
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

        $validators = $this->notifyHooks('users.hook.user.validate.edit', $reginfo, null, array(), new Zikula_Collection_HookValidationProviders())->getData();

        $errorMessages = array();
        $errorFields = array();
        if ($registrationErrors) {
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
            
            return new Zikula_Response_Ajax($returnValue);
        } elseif ($validators->hasErrors()) {
            $areaErrorCollections = $validators->getCollection();
            foreach ($areaErrorCollections as $area => $errorCollection) {
                $areaErrors = $errorCollection->getErrors();
                foreach ($areaErrors as $field => $message) {
                    $errorFields[] = $field;
                    $errorMessages[] = $message;
                }
            }
            $returnValue = array(
                'fields'    => $errorFields,
                'messages'  => $errorMessages,
            );
        } else {
            $returnValue = array(
                'fields'    => array(),
                'messages'  => array(),
            );
            
            return new Zikula_Response_Ajax($returnValue);
        }
    }
}
