<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Twig\Extension;

use Zikula\UsersModule\Twig\Extension\SimpleFunction\AuthenticationMethodSelectorFunction;
use Zikula\UsersModule\Twig\Extension\SimpleFunction\LoginFormFieldsFunction;

/**
 * Class RegistrationExtension
 */
class RegistrationExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulausersmodule_registration';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            // note: both functions are @deprecated
            new \Twig_SimpleFunction('authenticationMethodSelector', [new AuthenticationMethodSelectorFunction(), 'display'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('loginFormFields', [new LoginFormFieldsFunction(), 'display'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return [];
    }
}
