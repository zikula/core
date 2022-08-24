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

use DateTime;
use DateTimeZone;
use Zikula\LegalBundle\LegalConstant;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

/**
 * Helper class to process acceptance of policies.
 */
class AcceptPoliciesHelper
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly UserRepositoryInterface $userRepository,
        private readonly array $legalConfig
    ) {
    }

    /**
     * Retrieves flags indicating which policies are active.
     *
     * @return array An array containing flags indicating whether each policy is active or not
     */
    public function getActivePolicies(): array
    {
        $policies = $this->legalConfig['policies'];

        return [
            'privacyPolicy'           => $policies['privacy_policy']['enabled'],
            'termsOfUse'              => $policies['terms_of_use']['enabled'],
            'tradeConditions'         => $policies['trade_conditions']['enabled'],
            'cancellationRightPolicy' => $policies['cancellation_right_policy']['enabled'],
            'agePolicy'               => 0 !== $this->legalConfig['minimum_age'],
        ];
    }

    /**
     * Helper method to determine acceptance / confirmation states for current user.
     */
    private function determineAcceptanceState(int $uid = null, string $attributeName = ''): ?string
    {
        $acceptanceState = null;

        if (null !== $uid && !empty($uid) && is_numeric($uid) && $uid > 0) {
            if ($uid > UsersConstant::USER_ID_ADMIN) {
                /** @var UserEntity $user */
                $user = $this->userRepository->find($uid);
                $acceptanceState = $user->getAttributes()->containsKey($attributeName) ? $user->getAttributeValue($attributeName) : null;
            } else {
                // The special users (uid == UsersConstant::USER_ID_ADMIN or UsersConstant::USER_ID_ANONYMOUS) have always accepted all policies.
                $now = new \DateTime('now', new DateTimeZone('UTC'));
                $nowStr = $now->format(DateTime::ATOM);
                $acceptanceState = $nowStr;
            }
        }

        return $acceptanceState;
    }

    /**
     * Retrieves flags indicating which policies the user with the given uid has already accepted.
     */
    public function getAcceptedPolicies(int $uid = null): array
    {
        $termsOfUseAcceptedDateStr = $this->determineAcceptanceState($uid, LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED);
        $privacyPolicyAcceptedDateStr = $this->determineAcceptanceState($uid, LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED);
        $agePolicyConfirmedDateStr = $this->determineAcceptanceState($uid, LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED);
        $cancellationRightPolicyAcceptedDateStr = $this->determineAcceptanceState($uid, LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED);
        $tradeConditionsAcceptedDateStr = $this->determineAcceptanceState($uid, LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED);

        $termsOfUseAcceptedDate = $termsOfUseAcceptedDateStr ? new DateTime($termsOfUseAcceptedDateStr) : false;
        $privacyPolicyAcceptedDate = $privacyPolicyAcceptedDateStr ? new DateTime($privacyPolicyAcceptedDateStr) : false;
        $agePolicyConfirmedDate = $agePolicyConfirmedDateStr ? new DateTime($agePolicyConfirmedDateStr) : false;
        $cancellationRightPolicyAcceptedDate = $cancellationRightPolicyAcceptedDateStr ? new DateTime($cancellationRightPolicyAcceptedDateStr) : false;
        $tradeConditionsAcceptedDate = $tradeConditionsAcceptedDateStr ? new DateTime($tradeConditionsAcceptedDateStr) : false;

        $now = new DateTime();
        $termsOfUseAccepted = $termsOfUseAcceptedDate ? $termsOfUseAcceptedDate <= $now : false;
        $privacyPolicyAccepted = $privacyPolicyAcceptedDate ? $privacyPolicyAcceptedDate <= $now : false;
        $agePolicyConfirmed = $agePolicyConfirmedDate ? $agePolicyConfirmedDate <= $now : false;
        $cancellationRightPolicyAccepted = $cancellationRightPolicyAcceptedDate ? $cancellationRightPolicyAcceptedDate <= $now : false;
        $tradeConditionsAccepted = $tradeConditionsAcceptedDate ? $tradeConditionsAcceptedDate <= $now : false;

        return [
            'privacyPolicy'           => $privacyPolicyAccepted,
            'termsOfUse'              => $termsOfUseAccepted,
            'tradeConditions'         => $tradeConditionsAccepted,
            'cancellationRightPolicy' => $cancellationRightPolicyAccepted,
            'agePolicy'               => $agePolicyConfirmed,
        ];
    }

    /**
     * Determine whether the current user can view the acceptance/confirmation status of certain policies.
     *
     * If the current user is the subject user, then the user can always see his status for each policy. If the current user is not the
     * same as the subject user, then the current user can only see the status if he has ACCESS_MODERATE access for the policy.
     */
    public function getViewablePolicies(int $userId = null): array
    {
        $currentUid = $this->currentUserApi->get('uid');
        $isCurrentUser = null !== $userId && $userId === $currentUid;

        return [
            'privacyPolicy'           => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalBundle::privacyPolicy', '::', ACCESS_MODERATE),
            'termsOfUse'              => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalBundle::termsOfUse', '::', ACCESS_MODERATE),
            'tradeConditions'         => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalBundle::tradeConditions', '::', ACCESS_MODERATE),
            'cancellationRightPolicy' => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalBundle::cancellationRightPolicy', '::', ACCESS_MODERATE),
            'agePolicy'               => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalBundle::agePolicy', '::', ACCESS_MODERATE),
        ];
    }

    /**
     * Determine whether the current user can edit the acceptance/confirmation status of certain policies.
     *
     * The current user can only edit the status if he has ACCESS_EDIT access for the policy, whether he is the subject user or not. The ability to edit
     * status for login and new registrations is handled differently, and does not count on the output of this function.
     */
    public function getEditablePolicies(): array
    {
        return [
            'privacyPolicy'           => $this->permissionApi->hasPermission('ZikulaLegalBundle::privacyPolicy', '::', ACCESS_EDIT),
            'termsOfUse'              => $this->permissionApi->hasPermission('ZikulaLegalBundle::termsOfUse', '::', ACCESS_EDIT),
            'tradeConditions'         => $this->permissionApi->hasPermission('ZikulaLegalBundle::tradeConditions', '::', ACCESS_EDIT),
            'cancellationRightPolicy' => $this->permissionApi->hasPermission('ZikulaLegalBundle::cancellationRightPolicy', '::', ACCESS_EDIT),
            'agePolicy'               => $this->permissionApi->hasPermission('ZikulaLegalBundle::agePolicy', '::', ACCESS_EDIT),
        ];
    }
}
