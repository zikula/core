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

namespace Zikula\UsersModule\Api;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class CurrentUserApi implements CurrentUserApiInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    /**
     * @var bool
     */
    private $loggedIn;

    /**
     * @var \Zikula\UsersModule\Entity\UserEntity
     */
    private $user;

    /**
     * CurrentUser constructor.
     * @param SessionInterface $session
     * @param UserRepositoryInterface $repository
     */
    public function __construct(SessionInterface $session, UserRepositoryInterface $repository)
    {
        $this->loggedIn = false;
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function isLoggedIn()
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
     * Allows Twig to fetch properties without use of ArrayAccess
     *
     * ArrayAccess is problematic because Twig uses isset() to
     * check if property field exists, so it's not possible
     * to get using default values, ie, empty.
     *
     * @param $key
     * @param $args
     *
     * @return string
     */
    public function __call($key, $args)
    {
        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (!isset($this->user)) {
            $this->setUser();
        }
        $method = "get" . ucwords($key);
        if (method_exists($this->user, $method)) {
            return $this->user->{$method}();
        }

        return null;
    }

    /**
     * Get the uid from the session and create a user from the repository.
     * Default to Constant::USER_ID_ANONYMOUS
     */
    private function setUser()
    {
        $this->session->start();
        $uid = $this->session->get('uid', Constant::USER_ID_ANONYMOUS);
        $this->user = $this->repository->find($uid);
    }
}
