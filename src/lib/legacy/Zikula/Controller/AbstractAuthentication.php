<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Core\Exception\FatalErrorException;

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

    protected function getAuthenticationMethod($methodName)
    {
        return ModUtil::apiFunc($this->name, 'Authentication', 'getAuthenticationMethod', array('method' => $methodName), 'Zikula_Api_AbstractAuthentication');
    }

    /**
     * Render and return the portion of the HTML log-in form containing the fields needed by this authentication module in order to log in.
     *
     * @param array $args All parameters passed to this function.
     *
     * @todo In 1.5.0, throw exception here and do not allow the "getLoginFormFields" method.
     *
     * @return string the rendered HTML fragment containing the authentication module fields for the login form or block.
     */
    public function getLoginFormFieldsAction(array $args)
    {
        return $this->getLoginFormFields($args);
    }

    /**
     * @param array $args
     *
     * @deprecated Use "getLoginFormFieldsAction" instead.
     *
     * @return string
     */
    public function getLoginFormFields(array $args)
    {
        throw new \LogicException('This method must be overridden in concrete class');
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
     * @param array $args All parameters passed to this function.
     *
     * @todo In 1.5.0, move content from "getAuthenticationMethodSelector" method to here.
     *
     * @return string The rendered authentication method selector for the login page or block.
     */
    public function getAuthenticationMethodSelectorAction(array $args)
    {
        return $this->getAuthenticationMethodSelector($args);
    }

    /**
     * @param array $args
     *
     * @deprecated Use "getAuthenticationMethodSelectorAction" instead.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws FatalErrorException
     */
    public function getAuthenticationMethodSelector(array $args)
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

        if (!isset($args['form_action']) || !is_string($args['form_action'])) {
            throw new \InvalidArgumentException($this->__f('An invalid form action (\'%1$s\') was received.', array(
                    isset($args['form_action']) ? $args['form_action'] : 'NULL'))
            );
        }

        if (!isset($args['method']) || !is_string($args['method']) || !$this->supportsAuthenticationMethod($args['method'])) {
            throw new \InvalidArgumentException($this->__f('Error: An invalid method (\'%1$s\') was received.', array(
                    isset($args['method']) ? $args['method'] : 'NULL'))
            );
        }
        // End parameter extraction and error checking

        if ($this->authenticationMethodIsEnabled($args['method'])) {
            /** @var \Zikula\UsersModule\Helper\AuthenticationMethodHelper $authenticationMethod */
            $authenticationMethod = $this->getAuthenticationMethod($args['method']);
            $icon = $authenticationMethod->getIcon();
            $isFontAwesomeIcon = $authenticationMethod->isFontAwesomeIcon();

            $templateVars = array(
                'authentication_method'   => array(
                    'modname'   => $this->name,
                    'method'    => $args['method'],
                ),
                'is_selected'             => isset($args['is_selected']) && $args['is_selected'],
                'form_type'               => $args['form_type'],
                'form_action'             => $args['form_action'],
                'submit_text'             => $authenticationMethod->getShortDescription(),
                'icon'                    => $icon,
                'isFontAwesomeIcon'       => $isFontAwesomeIcon,
                'skipLoginFormFieldsPage' => $authenticationMethod->getSkipLoginFormFieldsPage() && $args['form_type'] === 'registration'
            );

            $view = new Zikula_View($this->serviceManager, 'ZikulaUsersModule', Zikula_View::CACHE_ENABLED);
            $view->assign($templateVars);

            $templateName = "Authentication/AuthenticationMethodSelector/{$args['form_type']}/Base.tpl";
            if (!$view->template_exists($templateName)) {
                $templateName = "Authentication/AuthenticationMethodSelector/Base/Base.tpl";
                if (!$view->template_exists($templateName)) {
                    throw new FatalErrorException($this->__f('A form fields template was not found for the %1$s method using form type \'%2$s\'.', array($args['method'], $args['form_type'])));
                }
            }

            return $this->response($view->fetch($templateName));
        } else {
            return $this->response('');
        }
    }

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
     *
     * @todo In 1.5.0, throw exception here and do not allow the "validateAuthenticationInformation" method.
     */
    public function validateAuthenticationInformationAction(array $args)
    {
        return $this->validateAuthenticationInformation($args);
    }

    /**
     * @param array $args
     *
     * @deprecated Use "validateAuthenticationInformationAction" instead.
     *
     * @return bool
     *
     * @throws \LogicException
     */
    public function validateAuthenticationInformation(array $args)
    {
        throw new \LogicException('This method must be overridden in concrete class');
    }
}
