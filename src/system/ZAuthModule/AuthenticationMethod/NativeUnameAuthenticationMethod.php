<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\AuthenticationMethod;

use Zikula\ZAuthModule\ZAuthConstant;

class NativeUnameAuthenticationMethod extends AbstractNativeAuthenticationMethod
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_UNAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->__('Native Uname');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->__('Allow a user to authenticate and login via Zikula\'s native user database with their username.');
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginFormClassName()
    {
        return 'Zikula\ZAuthModule\Form\Type\UnameLoginType';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginTemplateName($type = 'page', $position = 'left')
    {
        if ('block' == $type) {
            if ('topnav' == $position) {
                return 'ZikulaZAuthModule:Authentication:UnameLoginBlock.topnav.html.twig';
            }

            return 'ZikulaZAuthModule:Authentication:UnameLoginBlock.html.twig';
        }

        return 'ZikulaZAuthModule:Authentication:UnameLogin.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $data = [])
    {
        return $this->authenticateByField($data, 'uname');
    }
}
