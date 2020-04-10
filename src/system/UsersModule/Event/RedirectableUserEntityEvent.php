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

namespace Zikula\UsersModule\Event;

/**
 * An UserEntityEvent that adds the ability to set/get a redirectUrl.
 */
class RedirectableUserEntityEvent extends UserEntityEvent
{
    /**
     * @var string
     */
    private $redirectUrl = '';

    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
