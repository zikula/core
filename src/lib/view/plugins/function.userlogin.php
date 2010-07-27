<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View function to display the login box
 *
 * Example
 * {userlogin size=14 maxlength=25 maxlengthpass=20}
 *
 * Parameters:
 *  size           Size of text boxes (default=14)
 *  maxlength      Maximum length of text box for unamees (default=25)
 *  maxlengthpass  Maximum length of text box for password (default=20)
 *  class          Name of class  assigned to the login form
 *  value          The default value of the username input box
 *  js             Use javascript to automatically clear the default value (defaults to true)
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.userlogin.php::smarty_function_userlogin()
 *
 * @return string The welcome message.
 */
function smarty_function_userlogin($params, $view)
{
    if (!UserUtil::isLoggedIn()) {
        // set some defaults
        $size          = isset($params['size'])          ? $params['size']         : 14;
        $maxlength     = isset($params['maxlength'])     ? $params['maxlength']    : 25;
        $maxlengthpass = isset($params['maxlenthpass'])  ? $params['maxlenthpass'] : 20;
        $class         = isset($params['class'])         ? ' class="'.$params['class'].'"' : '';
        if (ModUtil::getVar('Users','loginviaoption') == 0) {
            $value = isset($params['value']) ? DataUtil::formatForDisplay($params['value']) : __('User name');
            $userNameLabel = __('User name');
            $inputName = 'uname';
        } else {
            $value = '';
            $userNameLabel = __('E-mail address');
            $inputName = 'email';
        }
        if (!isset($params['js']) || $params['js']) {
            $js = ' onblur="if (this.value==\'\')this.value=\''.$value.'\';" onfocus="if (this.value==\''.$value.'\')this.value=\'\';"';
        } else {
            $js = '';
        }

        // determine the current url so we can return the user to the correct place after login
        $returnurl = System::getCurrentUri();

        // b.plagge 20070821 - authkey is required
        $authkey = SecurityUtil::generateAuthKey('Users');

        $loginbox = '<form'.$class.' style="display:inline" action="'.DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'login')).'" method="post"><div>'."\n"
                   .'<input type="hidden" name="authid" value="' . DataUtil::formatForDisplay($authkey) .'" />'."\n"
                   .'<label for="userlogin_plugin_uname">' . $userNameLabel . '</label>&nbsp;'."\n"
                   .'<input type="text" name="' . $inputName . '" id="userlogin_plugin_uname" size="'.$size.'" maxlength="'.$maxlength.'" value="'.$value.'"'.$js.' />'."\n"
                   .'<label for="userlogin_plugin_pass">' . __('Password') . '</label>&nbsp;'."\n"
                   .'<input type="password" name="pass" id="userlogin_plugin_pass" size="'.$size.'" maxlength="'.$maxlengthpass.'" />'."\n";

        if (System::getVar('seclevel') <> 'high') {
            $loginbox .= '<input type="checkbox" value="1" name="rememberme" id="userlogin_plugin_rememberme" />'."\n"
                        .'<label for="userlogin_plugin_rememberme">' . __('Remember me') . '</label>&nbsp;'."\n";
        }

        $loginbox .= '<input type="hidden" name="url" value="' . DataUtil::formatForDisplay($returnurl) .'" />'."\n"
                    .'<input type="submit" value="' . __('Log in') . '" />'."\n"
                    .'</div></form>'."\n";
    } else {
        $loginbox = '';
    }

    return $loginbox;
}
