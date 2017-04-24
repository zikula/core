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

use Zikula\ZAuthModule\Form\Type\EitherLoginType;
use Zikula\ZAuthModule\ZAuthConstant;

class NativeEitherAuthenticationMethod extends AbstractNativeAuthenticationMethod
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_EITHER;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->__('Native Uname or Email');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->__('Allow a user to authenticate and login via Zikula\'s native user database with their username or email address.');
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginFormClassName()
    {
        return EitherLoginType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginTemplateName($type = 'page', $position = 'left')
    {
        if ($type == 'block') {
            if ($position == 'topnav') {
                return 'ZikulaZAuthModule:Authentication:EitherLoginBlock.topnav.html.twig';
            }

            return 'ZikulaZAuthModule:Authentication:EitherLoginBlock.html.twig';
        }

        return 'ZikulaZAuthModule:Authentication:EitherLogin.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $data = [])
    {
        $field = filter_var($data['either'], FILTER_VALIDATE_EMAIL) ? 'email' : 'uname';
        $data[$field] = $data['either'];

        return $this->authenticateByField($data, $field);
    }
}
