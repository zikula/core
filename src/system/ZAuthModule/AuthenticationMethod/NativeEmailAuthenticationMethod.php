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

class NativeEmailAuthenticationMethod extends AbstractNativeAuthenticationMethod
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_EMAIL;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->__('Native Email');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->__('Allow a user to authenticate and login via Zikula\'s native user database with their email address.');
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginFormClassName()
    {
        return 'Zikula\ZAuthModule\Form\Type\EmailLoginType';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginTemplateName($type = 'page', $position = 'left')
    {
        if ($type == 'block') {
            if ($position == 'topnav') {
                return 'ZikulaZAuthModule:Authentication:EmailLoginBlock.topnav.html.twig';
            }

            return 'ZikulaZAuthModule:Authentication:EmailLoginBlock.html.twig';
        }

        return 'ZikulaZAuthModule:Authentication:EmailLogin.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $data = [])
    {
        return $this->authenticateByField($data, 'email');
    }
}
