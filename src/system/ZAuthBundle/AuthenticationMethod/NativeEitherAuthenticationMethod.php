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

namespace Zikula\ZAuthBundle\AuthenticationMethod;

use Zikula\ZAuthBundle\Form\Type\EitherLoginType;
use Zikula\ZAuthBundle\ZAuthConstant;

class NativeEitherAuthenticationMethod extends AbstractNativeAuthenticationMethod
{
    public function getAlias(): string
    {
        return ZAuthConstant::AUTHENTICATION_METHOD_EITHER;
    }

    public function getDisplayName(): string
    {
        return $this->translator->trans('Native Uname or Email');
    }

    public function getDescription(): string
    {
        return $this->translator->trans('Allow a user to authenticate and login via Zikula\'s native user database with their username or email address.');
    }

    public function getLoginFormClassName(): string
    {
        return EitherLoginType::class;
    }

    public function getLoginTemplateName(string $type = 'page', string $position = 'left'): string
    {
        if ('block' === $type) {
            if ('topnav' === $position) {
                return '@ZikulaZAuth/Authentication/EitherLoginBlock.topnav.html.twig';
            }

            return '@ZikulaZAuth/Authentication/EitherLoginBlock.html.twig';
        }

        return '@ZikulaZAuth/Authentication/EitherLogin.html.twig';
    }

    public function authenticate(array $data = []): ?int
    {
        $field = 'email'; // default
        if (isset($data['either'])) {
            $field = filter_var($data['either'], FILTER_VALIDATE_EMAIL) ? 'email' : 'uname';
            $data[$field] = $data['either'];
        }

        return $this->authenticateByField($data, $field);
    }
}
