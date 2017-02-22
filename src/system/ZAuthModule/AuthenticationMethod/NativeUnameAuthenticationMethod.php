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
* @inheritDoc
     */
    public function getAlias()
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_UNAME;
    }

    /**
* @inheritDoc
     */
    public function getDisplayName()
    {
        return $this->translator->__('Native Uname');
    }

    /**
* @inheritDoc
     */
    public function getDescription()
    {
        return $this->translator->__('Allow a user to authenticate and login via Zikula\'s native user database with their username.');
    }

    /**
* @inheritDoc
     */
    public function getLoginFormClassName()
    {
        return 'Zikula\ZAuthModule\Form\Type\UnameLoginType';
    }

    /**
* @inheritDoc
     */
    public function getLoginTemplateName($type = 'page', $position = 'left')
    {
        if ($type == 'block') {
            if ($position == 'topnav') {
                return 'ZikulaZAuthModule:Authentication:UnameLoginBlock.topnav.html.twig';
            }

            return 'ZikulaZAuthModule:Authentication:UnameLoginBlock.html.twig';
        }

        return 'ZikulaZAuthModule:Authentication:UnameLogin.html.twig';
    }

    /**
* @inheritDoc
     */
    public function authenticate(array $data = [])
    {
        return $this->authenticateByField($data, 'uname');
    }
}
