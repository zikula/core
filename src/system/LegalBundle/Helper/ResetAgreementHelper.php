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

namespace Zikula\LegalBundle\Helper;

use Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\LegalBundle\LegalConstant;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Repository\UserAttributeRepositoryInterface;

/**
 * Helper class for resetting agreements of users.
 */
class ResetAgreementHelper
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly UserAttributeRepositoryInterface $attributeRepository,
        private readonly GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * Reset the agreement to the terms of use for a specific group of users, or all users.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     * @throws Exception Thrown in cases where expected data is not present or not in an expected form
     */
    public function reset(int $groupId): bool
    {
        if (!$this->permissionApi->hasPermission('ZikulaLegalBundle::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (!is_numeric($groupId) || $groupId < 0) {
            throw new Exception();
        }

        $attributeNames = [
            LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
            LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
            LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED,
            LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
            LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
        ];

        $members = [];
        if (0 !== $groupId) {
            $group = $this->groupRepository->find($groupId);
            if (empty($group)) {
                return false;
            }
            $members = $group->getUsers()->toArray();
            if (0 === count($members)) {
                return false;
            }
        }

        $this->userAttributeRepository->setEmptyValueWhereAttributeNameIn($attributeNames, $members);

        return true;
    }
}
