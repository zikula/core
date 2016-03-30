<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Exception\FatalErrorException;

class AuthenticationMethodSelectorFunction
{
    /**
     * @param string $formType
     * @param string $formAction
     * @param array $authenticationMethod
     * @param array $selectedAuthenticationMethod
     * @return string
     * @throws FatalErrorException
     */
    public function display($formType, $formAction, array $authenticationMethod, array $selectedAuthenticationMethod)
    {
        if (empty($authenticationMethod) || count($authenticationMethod) != 2) {
            throw new FatalErrorException(__f('An invalid \'%1$s\' parameter was received by the twig function \'%2$s\'.', array('$authenticationMethod', 'AuthenticationMethodSelector'), 'Zikula'));
        }
        if (!isset($authenticationMethod['modname']) || empty($authenticationMethod['modname']) || !is_string($authenticationMethod['modname'])) {
            throw new FatalErrorException(__f('An invalid authentication module was received by the twig function \'%1$s\'.', array('AuthenticationMethodSelector'), 'Zikula'));
        }

        if (!isset($authenticationMethod['method']) || empty($authenticationMethod['method']) || !is_string($authenticationMethod['method'])) {
            throw new FatalErrorException(__f('An invalid authentication method was received by the twig function \'%1$s\'.', array('AuthenticationMethodSelector'), 'Zikula'));
        }

        if (!empty($selectedAuthenticationMethod) && count($selectedAuthenticationMethod) != 2) {
            throw new FatalErrorException(__f('An invalid \'%1$s\' parameter was received by the twig function \'%2$s\'.', array('$selectedAuthenticationMethod', 'AuthenticationMethodSelector'), 'Zikula'));
        }

        if (!empty($selectedAuthenticationMethod)) {
            if (!isset($selectedAuthenticationMethod['modname']) || empty($selectedAuthenticationMethod['modname'])
                || !is_string($selectedAuthenticationMethod['modname'])
            ) {
                throw new FatalErrorException(__f('An invalid selected authentication module was received by the twig function \'%1$s\'.', array('AuthenticationMethodSelector'), 'Zikula'));
            }

            if (!isset($selectedAuthenticationMethod['method']) || empty($selectedAuthenticationMethod['method'])
                || !is_string($selectedAuthenticationMethod['method'])
            ) {
                throw new FatalErrorException(__f('An invalid selected authentication method was received by the twig function \'%1$s\'.', array('AuthenticationMethodSelector'), 'Zikula'));
            }

            $isSelected = ($authenticationMethod['modname'] == $selectedAuthenticationMethod['modname'])
                && ($authenticationMethod['method'] == $selectedAuthenticationMethod['method']);
        } else {
            $isSelected = false;
        }

        if (!is_string($formType) || empty($formType)) {
            throw new FatalErrorException(__f('An invalid \'%1$s\' parameter was received by the twig function \'%2$s\'.', array('$formType', 'AuthenticationMethodSelector')));
        }

        if (!is_string($formAction) || empty($formAction)) {
            throw new FatalErrorException(__f('An invalid \'%1$s\' parameter was received by the twig function \'%2$s\'.', array('$formAction', 'AuthenticationMethodSelector')));
        }

        $getSelectorArgs = array(
            'form_type'   => $formType,
            'form_action' => $formAction,
            'method'      => $authenticationMethod['method'],
            'is_selected' => $isSelected,
        );
        $content = \ModUtil::func($authenticationMethod['modname'], 'Authentication', 'getAuthenticationMethodSelector', $getSelectorArgs, 'Zikula_Controller_AbstractAuthentication');
        if ($content instanceof Response) {
            // Forward compatability. @deprecated Remove check in Core-2.0
            $content = $content->getContent();
        }

        return $content;
    }
}
