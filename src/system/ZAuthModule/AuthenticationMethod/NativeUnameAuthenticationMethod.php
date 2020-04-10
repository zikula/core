<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\AuthenticationMethod;

use Zikula\ZAuthModule\Form\Type\UnameLoginType;
use Zikula\ZAuthModule\ZAuthConstant;

class NativeUnameAuthenticationMethod extends AbstractNativeAuthenticationMethod
{
    public function getAlias(): string
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_UNAME;
    }

    public function getDisplayName(): string
    {
        return $this->translator->trans('Native Uname');
    }

    public function getDescription(): string
    {
        return $this->translator->trans('Allow a user to authenticate and login via Zikula\'s native user database with their username.');
    }

    public function getLoginFormClassName(): string
    {
        return UnameLoginType::class;
    }

    public function getLoginTemplateName(string $type = 'page', string $position = 'left'): string
    {
        if ('block' === $type) {
            if ('topnav' === $position) {
                return '@ZikulaZAuthModule/Authentication/UnameLoginBlock.topnav.html.twig';
            }

            return '@ZikulaZAuthModule/Authentication/UnameLoginBlock.html.twig';
        }

        return '@ZikulaZAuthModule/Authentication/UnameLogin.html.twig';
    }

    public function authenticate(array $data = []): ?int
    {
        return $this->authenticateByField($data);
    }
}
