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

use Psr\EventDispatcher\StoppableEventInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Occurs immediately prior to a log-in that is expected to succeed. (All prerequisites for a
 * successful login have been checked and are satisfied.) This event allows an extension to
 * intercept the login process and prevent a successful login from taking place.
 *
 * A handler that needs to veto a login attempt should call `stopPropagation()`.
 * This will prevent other handlers from receiving the event, will
 * return to the login process, and will prevent the login from taking place. A handler that
 * vetoes a login attempt should set an appropriate session flash message and give any additional
 * feedback to the user attempting to log in that might be appropriate.
 *
 * If vetoing the login, the 'returnUrl' property should be set to redirect the user to an appropriate action.
 * Also, a 'flash' property may be set to provide information to the user for the veto.
 *
 * Note: the user __will not__ be logged in at the point where the event handler is
 * executing. Any attempt to check a user's permissions, his logged-in status, or any
 * operation will return a value equivalent to what an anonymous (guest) user would see. Care
 * should be taken to ensure that sensitive operations done within a handler for this event
 * do not introduce breaches of security.
 */
class UserPreSuccessLoginEvent extends RedirectableUserEntityEvent implements StoppableEventInterface
{
    private $propagationStopped = false;

    /**
     * @var string
     */
    private $authenticationMethod;

    /**
     * @var array
     */
    private $flashes = [];

    public function __construct(UserEntity $userEntity, string $authenticationMethod)
    {
        parent::__construct($userEntity);
        $this->authenticationMethod = $authenticationMethod;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function getAuthenticationMethod(): string
    {
        return $this->authenticationMethod;
    }

    public function getFlashesAsString(): string
    {
        return implode('<br />', $this->flashes);
    }

    public function hasFlashes(): bool
    {
        return !empty($this->flashes);
    }

    public function addFlash(string $message): void
    {
        $this->flashes[] = $message;
    }
}
