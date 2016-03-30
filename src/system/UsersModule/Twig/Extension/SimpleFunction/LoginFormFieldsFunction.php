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

class LoginFormFieldsFunction
{
    /**
     * @param string $formType
     * @param array $authenticationMethod
     * @return string
     * @throws FatalErrorException
     */
    public function display($formType, array $authenticationMethod)
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

        if (!is_string($formType) || empty($formType)) {
            throw new FatalErrorException(__f('An invalid \'%1$s\' parameter was received by the twig function \'%2$s\'.', array('$formType', 'AuthenticationMethodSelector')));
        }

        $args = array(
            'form_type'     => $formType,
            'method'        => $authenticationMethod['method'],
        );
        $content = \ModUtil::func($authenticationMethod['modname'], 'Authentication', 'getLoginFormFields', $args, 'Zikula_Controller_AbstractAuthentication');
        if ($content instanceof Response) {
            // Forward compatability. @deprecated Remove check in Core-2.0
            $content = $content->getContent();
        }
        
        return $content;
    }
}
