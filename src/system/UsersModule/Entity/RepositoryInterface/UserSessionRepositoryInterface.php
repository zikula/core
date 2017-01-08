<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\UsersModule\Entity\UserSessionEntity;

interface UserSessionRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Return an array of UID's that have logged in since the provided datetime
     * @param \DateTime $dateTime
     * @return mixed
     */
    public function getUsersSince(\DateTime $dateTime);

    public function countUsersSince(\DateTime $dateTime);

    public function countGuestsSince(\DateTime $dateTime);

    public function persistAndFlush(UserSessionEntity $entity);

    public function removeAndFlush($id);

    public function gc($level, $inactiveMinutes, $days);
}
