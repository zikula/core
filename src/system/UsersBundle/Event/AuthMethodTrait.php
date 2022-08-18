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

namespace Zikula\UsersBundle\Event;

trait AuthMethodTrait
{
    private string $authenticationMethod = '';

    public function getAuthenticationMethod(): string
    {
        return $this->authenticationMethod;
    }
}
