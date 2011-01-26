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
     * Determines whether the type of login form passed in is valid or not.
     *
     * @param string $formType Either 'block' or 'page' for valid values.
     * 
     * @return boolean True if the form type is either 'block' or 'page'; otherwise false.
     */
    protected function formTypeIsValid($formType)
    {
        return ($formType == 'block') || ($formType == 'page');
    }

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
}