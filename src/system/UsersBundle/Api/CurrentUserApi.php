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

namespace Zikula\UsersBundle\Api;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Constant;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class CurrentUserApi implements CurrentUserApiInterface
{
    private bool $loggedIn;

    private UserEntity $user;

    public function __construct(private readonly RequestStack $requestStack, private readonly UserRepositoryInterface $repository)
    {
        $this->loggedIn = false;
    }

    public function isLoggedIn(): bool
    {
        if (!isset($this->user)) {
            $this->setUser();
        }
        if (isset($this->user) && $this->user->getUid() > Constant::USER_ID_ANONYMOUS) {
            $this->loggedIn = true;
        }

        return $this->loggedIn;
    }

    /**
     * Allows Twig to fetch properties without use of ArrayAccess.
     *
     * ArrayAccess is problematic because Twig uses isset() to
     * check if property field exists, so it's not possible
     * to get using default values, ie, empty.
     *
     * @param mixed $args
     * @return mixed
     */
    public function __call(string $key, $args)
    {
        return $this->get($key);
    }

    public function get(string $key)
    {
        if (!isset($this->user)) {
            $this->setUser();
        }
        if (!isset($this->user)) {
            return null;
        }
        $method = 'get' . ucwords($key);
        if (method_exists($this->user, $method)) {
            return $this->user->{$method}();
        }

        return null;
    }

    /**
     * Get the uid from the session and create a user from the repository.
     * Default to Constant::USER_ID_ANONYMOUS
     */
    private function setUser(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
            $userId = $session->get('uid', Constant::USER_ID_ANONYMOUS);
            $this->user = $this->repository->find($userId);
        }
    }
}
