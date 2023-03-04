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

use Symfony\Bundle\SecurityBundle\Security;
use Zikula\LegalBundle\LegalConstant;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

/**
 * Helper class to process acceptance of policies.
 */
class AcceptPoliciesHelper
{
    public function __construct(
        private readonly Security $security,
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
            if (UsersConstant::USER_ID_ADMIN < $uid) {
                /** @var User $user */
                $user = $this->userRepository->find($uid);
                $acceptanceState = $user->getAttributes()->containsKey($attributeName) ? $user->getAttributeValue($attributeName) : null;
            } else {
                // The special users (uid == UsersConstant::USER_ID_ADMIN or UsersConstant::USER_ID_ANONYMOUS) have always accepted all policies.
                $now = new \DateTime('now', new \DateTimeZone('UTC'));
                $nowStr = $now->format(\DateTime::ATOM);
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

        $termsOfUseAcceptedDate = $termsOfUseAcceptedDateStr ? new \DateTime($termsOfUseAcceptedDateStr) : false;
        $privacyPolicyAcceptedDate = $privacyPolicyAcceptedDateStr ? new \DateTime($privacyPolicyAcceptedDateStr) : false;
        $agePolicyConfirmedDate = $agePolicyConfirmedDateStr ? new \DateTime($agePolicyConfirmedDateStr) : false;
        $cancellationRightPolicyAcceptedDate = $cancellationRightPolicyAcceptedDateStr ? new \DateTime($cancellationRightPolicyAcceptedDateStr) : false;
        $tradeConditionsAcceptedDate = $tradeConditionsAcceptedDateStr ? new \DateTime($tradeConditionsAcceptedDateStr) : false;

        $now = new \DateTime();
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
     * same as the subject user, then he needs to be an editor.
     */
    public function getViewablePolicies(int $userId = null): array
    {
        $currentUser = $this->security->getUser();
        $isCurrentUser = null === $userId && null === $currentUser || null !== $userId && $userId === $currentUser?->getId();
        $isEditor = $this->security->isGranted('ROLE_EDITOR');

        return [
            'privacyPolicy'           => $isCurrentUser || $isEditor,
            'termsOfUse'              => $isCurrentUser || $isEditor,
            'tradeConditions'         => $isCurrentUser || $isEditor,
            'cancellationRightPolicy' => $isCurrentUser || $isEditor,
            'agePolicy'               => $isCurrentUser || $isEditor,
        ];
    }

    /**
     * Determine whether the current user can edit the acceptance/confirmation status of certain policies.
     *
     * The current user can only edit the status if he is an editor.
     */
    public function getEditablePolicies(): array
    {
        $isEditor = $this->security->isGranted('ROLE_EDITOR');

        return [
            'privacyPolicy'           => $isEditor,
            'termsOfUse'              => $isEditor,
            'tradeConditions'         => $isEditor,
            'cancellationRightPolicy' => $isEditor,
            'agePolicy'               => $isEditor,
        ];
    }
}
