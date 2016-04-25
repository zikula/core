<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Zikula_View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Access to user-initiated authentication actions for the Users module.
 */
class AuthenticationController extends \Zikula_Controller_AbstractAuthentication
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
     * @param mixed[] $args {
     * -------------------------------------
     * - string $args['form_type'] An indicator of the type of form the form fields will appear on.
     * - string $args['method']    The authentication method for which a selector should be returned.
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the $args array is invalid, or contains an invalid value.
     * @throws NotFoundHttpException if template doesn't exist
     */
    public function getLoginFormFieldsAction(array $args)
    {
        // Parameter extraction and error checking
        if (!isset($args) || !is_array($args)) {
            throw new \InvalidArgumentException($this->__('An invalid \'$args\' parameter was received.'));
        }

        if (!isset($args['form_type']) || !is_string($args['form_type'])) {
            throw new \InvalidArgumentException($this->__f('An invalid form type (\'%1$s\') was received.', array(
                isset($args['form_type']) ? $args['form_type'] : 'NULL'))
            );
        }

        if (!isset($args['method']) || !is_string($args['method']) || !$this->supportsAuthenticationMethod($args['method'])) {
            throw new \InvalidArgumentException($this->__f('An invalid method (\'%1$s\') was received.', array(
                isset($args['method']) ? $args['method'] : 'NULL'))
            );
        }
        // End parameter extraction and error checking

        if ($this->authenticationMethodIsEnabled($args['method'])) {
            $templateName = "Authentication/LoginFormFields/{$args['form_type']}/{$args['method']}.tpl";
            if (!$this->view->template_exists($templateName)) {
                $templateName = "Authentication/LoginFormFields/Default/{$args['method']}.tpl";
                if (!$this->view->template_exists($templateName)) {
                    $templateName = "Authentication/LoginFormFields/{$args['form_type']}/Default.tpl";
                    if (!$this->view->template_exists($templateName)) {
                        $templateName = "Authentication/LoginFormFields/Default/Default.tpl";
                        if (!$this->view->template_exists($templateName)) {
                            throw new NotFoundHttpException($this->__f('A form fields template was not found for the %1$s method using form type \'%2$s\'.', array($args['method'], $args['form_type'])));
                        }
                    }
                }
            }

            $this->view->assign('authentication_method', $args['method']);

            return $this->response($this->view->fetch($templateName));
        }
    }

    /**
     * Performs initial user-interface level validation on the user name and password received by the user from the login process.
     *
     * @param mixed[] $args {
     * -------------------------------------
     * - array $args['authenticationMethod'] The authentication method (selected either by the user or by the system) for which
     *                                          the credentials in $authenticationInfo were entered by the user. For the Users
     *                                          module, the 'modname' element should contain 'ZikulaUsersModule' and the 'method' element
     *                                          should contain either 'uname', 'email' or 'unameoremail'.
     * - array $args['authenticationInfo']   The user's credentials, as supplied by him on a log-in form on the log-in screen,
     *                                          log-in block, or some other equivalent control. For the Users module, it should
     *                                          contain the elements 'login_id' and 'pass'.
     *
     * @return boolean True if the authentication information (the user's credentials) pass initial user-interface level validation;
     *                  otherwise false and an error status message is set.
     *
     * @throws \InvalidArgumentException|\BadMethodCallException Thrown if no authentication module name or method is specified, or if the module name or method
     *                                  is invalid for this module.
     */
    public function validateAuthenticationInformationAction(array $args)
    {
        $validates = false;

        $authenticationMethod = isset($args['authenticationMethod']) ? $args['authenticationMethod'] : array();
        $authenticationInfo   = isset($args['authenticationInfo']) ? $args['authenticationInfo'] : array();

        if (!is_array($authenticationMethod) || empty($authenticationMethod) || !isset($authenticationMethod['modname'])) {
            throw new \InvalidArgumentException($this->__('The authentication module name was not specified during an attempt to validate user authentication information.'));
        } elseif ($authenticationMethod['modname'] != 'ZikulaUsersModule') {
            throw new \InvalidArgumentException($this->__f('Attempt to validate authentication information with incorrect authentication module. Credentials should be validated with the \'%1$s\' module instead.', array($authenticationMethod['modname'])));
        }

        if (!isset($authenticationMethod['method'])) {
            throw new \BadMethodCallException($this->__('The authentication method name was not specified during an attempt to validate user authentication information.'));
        } elseif (($authenticationMethod['method'] != 'uname') && ($authenticationMethod['method'] != 'email') && ($authenticationMethod['method'] != 'unameoremail')) {
            throw new \BadMethodCallException($this->__f('Unknown authentication method (\'%1$s\') while attempting to validate user authentication information in the Users module.', array($authenticationMethod['method'])));
        }

        if (!is_array($authenticationInfo) || empty($authenticationInfo) || !isset($authenticationInfo['login_id'])
                || !is_string($authenticationInfo['login_id'])
                ) {
            // This is an internal error that the user cannot recover from, and should not happen (it is an exceptional situation).
            if ($authenticationMethod['method'] == 'uname') {
                throw new \InvalidArgumentException($this->__('A user name was not specified, or the user name provided was invalid.'));
            } elseif ($authenticationMethod['method'] == 'email') {
                throw new \InvalidArgumentException($this->__('An e-mail address was not specified, or the e-mail address provided was invalid.'));
            } elseif ($authenticationMethod['method'] == 'unameoremail') {
                throw new \InvalidArgumentException($this->__('An user name / e-mail address was not specified, or the user name / e-mail address provided was invalid.'));
            }
        }

        if (!isset($authenticationInfo['pass']) || !is_string($authenticationInfo['pass'])) {
            // This is an internal error that the user cannot recover from, and should not happen (it is an exceptional situation).
            throw new \InvalidArgumentException($this->__('A password was not specified, or the password provided was invalid.'));
        }

        // No need to be too fancy or too specific here. If the login id (the uname or email) is not empty, then that's sufficient.
        // If we are too specific here, then we are giving a potential hacker too much information about how the authentication process
        // works and what is expected. Just validate it enough so that a lookup can be performed.
        if (!empty($authenticationInfo['login_id'])) {
            if (!empty($authenticationInfo['pass'])) {
                $validates = true;
            } else {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide a password.'));
            }
        } elseif (empty($authenticationInfo['pass'])) {
            if ($authenticationMethod['method'] == 'uname') {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide a user name and password.'));
            } elseif ($authenticationMethod['method'] == 'email') {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide an e-mail address and password.'));
            } elseif ($authenticationMethod['method'] == 'unameoremail') {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide a user name / e-mail address and password.'));
            }
        } else {
            if ($authenticationMethod['method'] == 'uname') {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide a user name.'));
            } elseif ($authenticationMethod['method'] == 'email') {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide an e-mail address.'));
            } elseif ($authenticationMethod['method'] == 'unameoremail') {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Please provide a user name / e-mail address.'));
            }
        }

        return $validates;
    }
}
