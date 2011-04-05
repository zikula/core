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
        // Set caching to false by default.
        $this->view->setCaching(false);
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
}
