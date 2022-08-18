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

namespace Zikula\LegalModule\Helper;

use Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\GroupsModule\Repository\GroupRepositoryInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Repository\UserAttributeRepositoryInterface;

/**
 * Helper class for resetting agreements of users.
 */
class ResetAgreementHelper
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var UserAttributeRepositoryInterface
     */
    private $userAttributeRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    public function __construct(
        PermissionApiInterface $permissionApi,
        UserAttributeRepositoryInterface $attributeRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->permissionApi = $permissionApi;
        $this->userAttributeRepository = $attributeRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Reset the agreement to the terms of use for a specific group of users, or all users.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     * @throws Exception Thrown in cases where expected data is not present or not in an expected form
     */
    public function reset(int $groupId): bool
    {
        if (!$this->permissionApi->hasPermission(LegalConstant::MODNAME . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (!is_numeric($groupId) || $groupId < 0) {
            throw new Exception();
        }

        $attributeNames = [
            LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
            LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
            LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
            LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
            LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED
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
