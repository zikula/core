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

namespace Zikula\UsersModule\Event;

use Zikula\UsersModule\Entity\UserEntity;

/**
 * An UserEntityEvent that adds the ability to set/get a redirectUrl.
 */
class RedirectableUserEntityEvent extends UserEntityEvent
{
    /**
     * @var string
     */
    private $redirectUrl;

    public function __construct(UserEntity $userEntity, string $redirectUrl = '')
    {
        parent::__construct($userEntity);
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
