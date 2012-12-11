<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Access to user-initiated authentication actions for the Users module.
 */
class Users_Controller_Authentication extends Zikula_Controller_AbstractAuthentication
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
     * Renders the template that displays the input fields for the authentication module in the Users module's login block.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * - string $args['form_type'] An indicator of the type of form the form fields will appear on.
     * - string $args['method']    The authentication method for which a selector should be returned.
     *
     * @param array $args The parameters for this function.
     *
     * @return string The rendered template.
     *
     * @throws Zikula_Exception_Fatal Thrown if the $args array is invalid, or contains an invalid value.
     */
    public function getLoginFormFields(array $args)
    {
        // Parameter extraction and error checking
        if (!isset($args) || !is_array($args)) {
            throw new Zikula_Exception_Fatal($this->__('An invalid \'$args\' parameter was received.'));
        }

        if (!isset($args['form_type']) || !is_string($args['form_type'])) {
            throw new Zikula_Exception_Fatal($this->__('An invalid form type (\'%1$s\') was received.', array(
                isset($args['form_type']) ? $args['form_type'] : 'NULL'))
            );
        }

        if (!isset($args['method']) || !is_string($args['method']) || !$this->supportsAuthenticationMethod($args['method'])) {
            throw new Zikula_Exception_Fatal($this->__('An invalid method (\'%1$s\') was received.', array(
                isset($args['method']) ? $args['method'] : 'NULL'))
            );
        }
        // End parameter extraction and error checking

        if ($this->authenticationMethodIsEnabled($args['method'])) {
            $templateName = mb_strtolower("users_auth_loginformfields_{$args['form_type']}_{$args['method']}.tpl");
            if (!$this->view->template_exists($templateName)) {
                $templateName = mb_strtolower("users_auth_loginformfields_default_{$args['method']}.tpl");
                if (!$this->view->template_exists($templateName)) {
                    $templateName = mb_strtolower("users_auth_loginformfields_{$args['form_type']}_default.tpl");
                    if (!$this->view->template_exists($templateName)) {
                        $templateName = mb_strtolower("users_auth_loginformfields_default_default.tpl");
                        if (!$this->view->template_exists($templateName)) {
                            throw new Zikula_Exception_Fatal($this->__f('A form fields template was not found for the %1$s method using form type \'%2$s\'.', array($method, $args['form_type'])));
                        }
                    }
                }
            }

            return $this->view->assign('authentication_method', $args['method'])
                    ->fetch($templateName);
        }
    }

    /**
     * Renders the template that displays the authentication module's icon in the Users module's login block.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * - string $args['form_type']   An indicator of the type of form on which the selector will appear.
     * - string $args['form_action'] The URL to which the selector form should submit.
     * - string $args['method']      The authentication method for which a selector should be returned.
     *
     * @param array $args The parameters for this function.
     *
     * @return string The rendered template.
     *
     * @throws Zikula_Exception_Fatal Thrown if the $args array is invalid, or contains an invalid value.
     */
    public function getAuthenticationMethodSelector(array $args)
    {
        // Parameter extraction and error checking
        if (!isset($args) || !is_array($args)) {
            throw new Zikula_Exception_Fatal($this->__('An invalid \'$args\' parameter was received.'));
        }

        if (!isset($args['form_type']) || !is_string($args['form_type'])) {
            throw new Zikula_Exception_Fatal($this->__f('An invalid form type (\'%1$s\') was received.', array(
                isset($args['form_type']) ? $args['form_type'] : 'NULL'))
            );
        }

        if (!isset($args['form_action']) || !is_string($args['form_action'])) {
            throw new Zikula_Exception_Fatal($this->__f('An invalid form action (\'%1$s\') was received.', array(
                isset($args['form_action']) ? $args['form_action'] : 'NULL'))
            );
        }

        if (!isset($args['method']) || !is_string($args['method']) || !$this->supportsAuthenticationMethod($args['method'])) {
            throw new Zikula_Exception_Fatal($this->__f('Error: An invalid method (\'%1$s\') was received.', array(
                isset($args['method']) ? $args['method'] : 'NULL'))
            );
        }
        // End parameter extraction and error checking

        if ($this->authenticationMethodIsEnabled($args['method'])) {
            $templateVars = array(
                'authentication_method' => array(
                    'modname'   => $this->name,
                    'method'    => $args['method'],
                ),
                'is_selected'           => isset($args['is_selected']) && $args['is_selected'],
                'form_type'             => $args['form_type'],
                'form_action'           => $args['form_action'],
            );

            $templateName = mb_strtolower("users_auth_authenticationmethodselector_{$args['form_type']}_{$args['method']}.tpl");
            if (!$this->view->template_exists($templateName)) {
                $templateName = mb_strtolower("users_auth_authenticationmethodselector_default_{$args['method']}.tpl");
                if (!$this->view->template_exists($templateName)) {
                    $templateName = mb_strtolower("users_auth_authenticationmethodselector_{$args['form_type']}_default.tpl");
                    if (!$this->view->template_exists($templateName)) {
                        $templateName = mb_strtolower("users_auth_authenticationmethodselector_default_default.tpl");
                        if (!$this->view->template_exists($templateName)) {
                            throw new Zikula_Exception_Fatal($this->__f('An authentication method selector template was not found for method \'%1$s\' using form type \'%2$s\'.', array($args['method'], $args['form_type'])));
                        }
                    }
                }
            }

            return $this->view->assign($templateVars)
                    ->fetch($templateName);
        }
    }

    /**
     * Performs initial user-interface level validation on the user name and password received by the user from the login process.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * - array $args['authenticationMethod'] The authentication method (selected either by the user or by the system) for which
     *                                          the credentials in $authenticationInfo were entered by the user. For the Users
     *                                          module, the 'modname' element should contain 'Users' and the 'method' element
     *                                          should contain either 'uname' or 'email'.
     * - array $args['authenticationInfo']   The user's credentials, as supplied by him on a log-in form on the log-in screen,
     *                                          log-in block, or some other equivalent control. For the Users module, it should
     *                                          contain the elements 'login_id' and 'pass'.
     *
     * @param array $args The parameters for this function.
     *
     * @return boolean True if the authentication information (the user's credentials) pass initial user-interface level validation;
     *                  otherwise false and an error status message is set.
     *
     * @throws Zikula_Exception_Fatal Thrown if no authentication module name or method is specified, or if the module name or method
     *                                  is invalid for this module.
     */
    public function validateAuthenticationInformation(array $args)
    {
        $validates = false;

        $authenticationMethod = isset($args['authenticationMethod']) ? $args['authenticationMethod'] : array();
        $authenticationInfo   = isset($args['authenticationInfo']) ? $args['authenticationInfo'] : array();

        if (!is_array($authenticationMethod) || empty($authenticationMethod) || !isset($authenticationMethod['modname'])) {
            throw new Zikula_Exception_Fatal($this->__('The authentication module name was not specified during an attempt to validate user authentication information.'));
        } elseif ($authenticationMethod['modname'] != 'Users') {
            throw new Zikula_Exception_Fatal($this->__f('Attempt to validate authentication information with incorrect authentication module. Credentials should be validated with the \'%1$s\' module instead.', array($authenticationMethod['modname'])));
        }

        if (!isset($authenticationMethod['method'])) {
            throw new Zikula_Exception_Fatal($this->__('The authentication method name was not specified during an attempt to validate user authentication information.'));
        } elseif (($authenticationMethod['method'] != 'uname') && ($authenticationMethod['method'] != 'email')) {
            throw new Zikula_Exception_Fatal($this->__f('Unknown authentication method (\'%1$s\') while attempting to validate user authentication information in the Users module.', array($authenticationMethod['method'])));
        }

        if (!is_array($authenticationInfo) || empty($authenticationInfo) || !isset($authenticationInfo['login_id'])
                || !is_string($authenticationInfo['login_id'])
                ) {
            // This is an internal error that the user cannot recover from, and should not happen (it is an exceptional situation).
            if ($authenticationMethod['method'] == 'uname') {
                throw new Zikula_Exception_Fatal($this->__('A user name was not specified, or the user name provided was invalid.'));
            } else {
                throw new Zikula_Exception_Fatal($this->__('An e-mail address was not specified, or the e-mail address provided was invalid.'));
            }
        }

        if (!isset($authenticationInfo['pass']) || !is_string($authenticationInfo['pass'])) {
            // This is an internal error that the user cannot recover from, and should not happen (it is an exceptional situation).
            throw new Zikula_Exception_Fatal($this->__('A password was not specified, or the password provided was invalid.'));
        }

        // No need to be too fancy or too specific here. If the login id (the uname or email) is not empty, then that's sufficient.
        // If we are too specific here, then we are giving a potential hacker too much information about how the authentication process
        // works and what is expected. Just validate it enough so that a lookup can be performed.
        if (!empty($authenticationInfo['login_id'])) {
            if (!empty($authenticationInfo['pass'])) {
                $validates = true;
            } else {
                $this->registerError($this->__('Please provide a password.'));
            }
        } elseif (empty($authenticationInfo['pass'])) {
            if ($authenticationMethod['method'] == 'uname') {
                $this->registerError($this->__('Please provide a user name and password.'));
            } else {
                $this->registerError($this->__('Please provide an e-mail address and password.'));
            }
        } else {
            if ($authenticationMethod['method'] == 'uname') {
                $this->registerError($this->__('Please provide a user name.'));
            } else {
                $this->registerError($this->__('Please provide an e-mail address.'));
            }
        }

        return $validates;
    }
}
