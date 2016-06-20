<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Api;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class CurrentUserApi
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
     * Check if current user is logged in.
     * @return boolean
     */
    public function isLoggedIn()
    {
        if (!isset($this->user)) {
            if ($uid = $this->session->get('uid')) {
                $this->user = $this->repository->find($uid);
                $this->loggedIn = true;
            }
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
     * Gets key
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        if ($this->isLoggedIn()) {
            $method = "get" . ucwords($key);

            return $this->user->$method();
        }

        return null;
    }
}
