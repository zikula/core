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
     * - string $args['formType'] Either 'page' or 'block' indicating whether the selector will appear on a full page or a block.
     * - string $args['method']   The authentication method for which a selector should be returned.
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

        if (!isset($args['formType']) || !is_string($args['formType']) || !$this->formTypeIsValid($args['formType'])) {
            throw new Zikula_Exception_Fatal($this->__('An invalid formType (\'%1$s\') was received.', array(
                isset($args['formType']) ? $args['formType'] : 'NULL'))
            );
        }

        if (!isset($args['method']) || !is_string($args['method']) || !$this->supportsAuthenticationMethod($args['method'])) {
            throw new Zikula_Exception_Fatal($this->__('An invalid method (\'%1$s\') was received.', array(
                isset($args['method']) ? $args['method'] : 'NULL'))
            );
        }
        // End parameter extraction and error checking

        if ($this->authenticationMethodIsEnabled($args['method'])) {
            return $this->view->assign('authentication_method', $args['method'])
                    ->fetch(mb_strtolower("users_auth_loginformfields_{$args['formType']}.tpl"));
        } else {
            return;
        }
    }

    /**
     * Renders the template that displays the authentication module's icon in the Users module's login block.
     * 
     * Parameters passed in the $args array:
     * -------------------------------------
     * - string $args['formType'] Either 'page' or 'block' indicating whether the selector will appear on a full page or a block.
     * - string $args['method']   The authentication method for which a selector should be returned.
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

        if (!isset($args['formType']) || !is_string($args['formType']) || !$this->formTypeIsValid($args['formType'])) {
            throw new Zikula_Exception_Fatal($this->__f('An invalid formType (\'%1$s\') was received.', array(
                isset($args['formType']) ? $args['formType'] : 'NULL'))
            );
        }

        if (!isset($args['method']) || !is_string($args['method']) || !$this->supportsAuthenticationMethod($args['method'])) {
            throw new Zikula_Exception_Fatal($this->__f('Error: An invalid method (\'%1$s\') was received.', array(
                isset($args['method']) ? $args['method'] : 'NULL'))
            );
        }
        // End parameter extraction and error checking

        if ($this->authenticationMethodIsEnabled($args['method'])) {
            $authenticationMethod = array(
                'modname'   => $this->name,
                'method'    => $args['method'],
            );

            return $this->view->assign('authentication_method', $authenticationMethod)
                    ->assign('is_selected', isset($args['is_selected']) && $args['is_selected'])
                    ->fetch(mb_strtolower("users_auth_authenticationmethodselector_{$args['formType']}.tpl"));
        } else {
            return;
        }
    }
}
