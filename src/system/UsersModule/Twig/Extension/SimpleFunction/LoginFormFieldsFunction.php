<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Exception\FatalErrorException;

/**
 * @deprecated
 * Class LoginFormFieldsFunction
 */
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
