<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Controller
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract authentication module controller.
 */
abstract class Zikula_Controller_AbstractAuthentication extends Zikula_AbstractController
{
    /**
     * Indicates whether the authentication method is supported by this authentication module or not.
     *
     * Convenience access to the api function of the same name from within the controller.
     *
     * @param string $methodName The name of the authentication method.
     *
     * @return boolean True if the method is supported by this module; otherwise false.
     */
    protected function supportsAuthenticationMethod($methodName)
    {
        return ModUtil::apiFunc($this->name, 'Authentication', 'supportsAuthenticationMethod', array('method' => $methodName), 'Zikula_Api_AbstractAuthentication');
    }

    /**
     * Indicates whether the supported authentication method is enabled for use.
     *
     * Convenience access to the api function of the same name from within the controller.
     *
     * @param string $methodName The name of the authentication method.
     *
     * @return boolean True if the method is enabled for use; otherwise false.
     */
    protected function authenticationMethodIsEnabled($methodName)
    {
        return ModUtil::apiFunc($this->name, 'Authentication', 'isEnabledForAuthentication', array('method' => $methodName), 'Zikula_Api_AbstractAuthentication');
    }

    /**
     * Render and return the portion of the HTML log-in form containing the fields needed by this authentication module in order to log in.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return string the rendered HTML fragment containing the authentication module fields for the login form or block.
     */
    abstract public function getLoginFormFields(array $args);

    /**
     * Render and return an authentication method selector for the login page form or login block form.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return string The rendered authentication method selector for the login page or block.
     */
    abstract public function getAuthenticationMethodSelector(array $args);

    /**
     * Performs initial user-interface level validation on the authentication information received by the user from the login process.
     *
     * Each authentication method is free to define its own validation of the authentication information (user name and
     * password, or the equivalient for the authentication method), however the validation performed should be at the
     * user interface level. In other words, if all authentication information fields required by the authentication
     * method are supplied and the data is supplied in the proper form, then this validation will likely succeed, whereas
     * the actual attempt to log in with those credentials may still fail because the supplied information does not point
     * to a user. Likewise, this function may indicate that validation succeeds, but if the password (or password equivalent)
     * does not match that on file for the user to whom the credentials resolve then the attempt to log in with those
     * credentials may still fail.
     *
     * If this function returns true, indicating that validation is successful, then it *must be possible* (although not
     * guaranteed) to successfully log in with the validated credentials. If this function returns false, indicating that
     * validation was not successful, then it *must be impossible* to use the supplied credentials to log in under any
     * circumstances at all. When this function returns false, it must also set the appropriate error message for the
     * user's redirection to an appropriate page by the calling function (or it must ensure that one has been set by some
     * subordinate function).
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * - array $args['authenticationMethod'] The authentication method (selected either by the user or by the system) for which
     *                                          the credentials in $authenticationInfo were entered by the user. This array will
     *                                          contain 'modname', the name of the module that defines the authentication method,
     *                                          and 'method', the name of the specific method being used.
     * - array $args['authenticationInfo']   The user's credentials, as supplied by him on a log-in form on the log-in screen,
     *                                          log-in block, or some other equivalent control. The contents of the array are
     *                                          specified by the specific authentication method, but typically contains an
     *                                          equivalent to a user name, and possibly an equivalent to a password (especially
     *                                          if the authentication method does not perform external third-party authentication
     *                                          via a federated authentication service).
     *
     * @param array $args The parameters for this function.
     *
     * @return boolean True if the authentication information (the user's credentials) pass initial user-interface level validation;
     *                  otherwise false and an error status message is set.
     */
    abstract public function validateAuthenticationInformation(array $args);
}
