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

interface UserSessionRepositoryInterface extends ObjectRepository, Selectable
{
    public function getUsersSince(\DateTime $dateTime);

    public function countUsersSince(\DateTime $dateTime);

    public function countGuestsSince(\DateTime $dateTime);
}
