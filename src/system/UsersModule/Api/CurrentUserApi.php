<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Api;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\UsersModule\Entity\Repository\UserRepository;

class CurrentUserApi
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var UserRepository
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
     * @param UserRepository $repository
     */
    public function __construct(SessionInterface $session, UserRepository $repository)
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
