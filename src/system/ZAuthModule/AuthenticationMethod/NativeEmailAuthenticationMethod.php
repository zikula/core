<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\AuthenticationMethod;

use Zikula\ZAuthModule\Form\Type\EmailLoginType;
use Zikula\ZAuthModule\ZAuthConstant;

class NativeEmailAuthenticationMethod extends AbstractNativeAuthenticationMethod
{
    public function getAlias(): string
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_EMAIL;
    }

    public function getDisplayName(): string
    {
        return $this->translator->__('Native Email');
    }

    public function getDescription(): string
    {
        return $this->translator->__('Allow a user to authenticate and login via Zikula\'s native user database with their email address.');
    }

    public function getLoginFormClassName(): string
    {
        return EmailLoginType::class;
    }

    public function getLoginTemplateName(string $type = 'page', string $position = 'left'): string
    {
        if ('block' === $type) {
            if ('topnav' === $position) {
                return 'ZikulaZAuthModule:Authentication:EmailLoginBlock.topnav.html.twig';
            }

            return 'ZikulaZAuthModule:Authentication:EmailLoginBlock.html.twig';
        }

        return 'ZikulaZAuthModule:Authentication:EmailLogin.html.twig';
    }

    public function authenticate(array $data = []): ?int
    {
        return $this->authenticateByField($data, 'email');
    }
}
