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

use Nucleos\UserBundle\Model\GroupManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\LegalBundle\LegalConstant;
use Zikula\UsersBundle\Repository\UserAttributeRepositoryInterface;

/**
 * Helper class for resetting agreements of users.
 */
class ResetAgreementHelper
{
    public function __construct(
        private readonly Security $security,
        private readonly GroupManager $groupManager,
        private readonly UserAttributeRepositoryInterface $attributeRepository
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
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        if (!is_numeric($groupId) || 0 > $groupId) {
            throw new \Exception();
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
            $group = $this->groupManager->findGroupBy(['id' => $groupId]);
            if (null === $group) {
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
