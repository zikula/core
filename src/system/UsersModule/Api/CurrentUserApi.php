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

namespace Zikula\UsersModule\Api;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class CurrentUserApi implements CurrentUserApiInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    /**
     * @var bool
     */
    private $loggedIn;

    /**
     * @var UserEntity
     */
    private $user;

    public function __construct(RequestStack $requestStack, UserRepositoryInterface $repository)
    {
        $this->loggedIn = false;
        $this->requestStack = $requestStack;
        $this->repository = $repository;
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
            $session->start();
            $userId = $session->get('uid', Constant::USER_ID_ANONYMOUS);
        } else {
            $userId = Constant::USER_ID_ANONYMOUS;
        }
        $this->user = $this->repository->find($userId);
    }
}
