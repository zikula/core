<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\UsersModule\Constant as UsersConstant;

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
function smarty_function_userlogin($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : false;
    if (!UserUtil::isLoggedIn()) {
        // set some defaults
        $size          = isset($params['size'])          ? $params['size']         : 14;
        $maxlength     = isset($params['maxlength'])     ? $params['maxlength']    : 25;
        $maxlengthpass = isset($params['maxlenthpass'])  ? $params['maxlenthpass'] : 20;
        $class         = isset($params['class'])         ? ' class="'.$params['class'].'"' : '';

        if (ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::LOGIN_METHOD_UNAME) == UsersConstant::LOGIN_METHOD_EMAIL) {
            $value = isset($params['value']) ? DataUtil::formatForDisplay($params['value']) : __('E-mail address');
            $userNameLabel = __('E-mail address');
            $methodName = 'email';
        } else {
            $value = isset($params['value']) ? DataUtil::formatForDisplay($params['value']) : __('User name');
            $userNameLabel = __('User name');
            $methodName = 'uname';
        }
        if (!isset($params['js']) || $params['js']) {
            $js = ' onblur="if (this.value==\'\')this.value=\''.$value.'\';" onfocus="if (this.value==\''.$value.'\')this.value=\'\';"';
        } else {
            $js = '';
        }

        // determine the current url so we can return the user to the correct place after login
        $returnurl = System::getCurrentUri();

        $csrftoken = SecurityUtil::generateCsrfToken();

        $loginbox = '<form'.$class.' style="display:inline" action="'.DataUtil::formatForDisplay(ModUtil::url('ZikulaUsersModule', 'user', 'login')).'" method="post"><div>'."\n"
                   .'<input type="hidden" name="csrftoken" value="' . $csrftoken .'" />'."\n"
                   .'<input type="hidden" name="authentication_method[modname]" value="Users" />'."\n"
                   .'<input type="hidden" name="authentication_method[method]" value="'. $methodName .'" />'."\n"
                   .'<label for="userlogin_plugin_uname">' . $userNameLabel . '</label>&nbsp;'."\n"
                   .'<input type="text" name="authentication_info[login_id]" id="userlogin_plugin_uname" size="'.$size.'" maxlength="'.$maxlength.'" value="'.$value.'"'.$js.' />'."\n"
                   .'<label for="userlogin_plugin_pass">' . __('Password') . '</label>&nbsp;'."\n"
                   .'<input type="password" name="authentication_info[pass]" id="userlogin_plugin_pass" size="'.$size.'" maxlength="'.$maxlengthpass.'" />'."\n";

        if (System::getVar('seclevel') != 'high') {
            $loginbox .= '<input type="checkbox" value="1" name="rememberme" id="userlogin_plugin_rememberme" />'."\n"
                        .'<label for="userlogin_plugin_rememberme">' . __('Remember me') . '</label>&nbsp;'."\n";
        }

        $loginbox .= '<input type="hidden" name="returnurl" value="' . DataUtil::formatForDisplay($returnurl) .'" />'."\n"
                    .'<input type="submit" value="' . __('Log in') . '" />'."\n"
                    .'</div></form>'."\n";
    } else {
        $loginbox = '';
    }

    if ($assign) {
        $view->assign($assign, $loginbox);
    } else {
        return $loginbox;
    }
}
