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
use Zikula\UsersModule\Constant;

interface UserAttributeRepositoryInterface extends ObjectRepository, Selectable
{
    public function setEmptyValueWhereAttributeNameIn(
        array $attributeNames,
        array $users = [],
        array $forbiddenUsers = [Constant::USER_ID_ADMIN, Constant::USER_ID_ANONYMOUS]
    );
}
