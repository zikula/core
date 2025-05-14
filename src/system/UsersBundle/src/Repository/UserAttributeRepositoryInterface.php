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

namespace Zikula\UsersBundle\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\UsersBundle\UsersConstant;

interface UserAttributeRepositoryInterface extends ObjectRepository, Selectable
{
    public function setEmptyValueWhereAttributeNameIn(
        array $attributeNames,
        array $users = [],
        array $forbiddenUsers = [UsersConstant::USER_ID_ADMIN, UsersConstant::USER_ID_ANONYMOUS]
    ): mixed;
}
