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

use DateTime;
use DateTimeZone;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Repository\UserRepositoryInterface;

/**
 * Helper class to process acceptance of policies.
 */
class AcceptPoliciesHelper
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var array
     */
    private $moduleVars;

    public function __construct(
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        VariableApiInterface $variableApi
    ) {
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->userRepository = $userRepository;
        $this->moduleVars = $variableApi->getAll('ZikulaLegalModule');
    }

    /**
     * Retrieves flags indicating which policies are active.
     *
     * @return array An array containing flags indicating whether each policy is active or not
     */
    public function getActivePolicies(): array
    {
        $termsOfUseActive = $this->moduleVars[LegalConstant::MODVAR_TERMS_ACTIVE] ?? false;
        $privacyPolicyActive = $this->moduleVars[LegalConstant::MODVAR_PRIVACY_ACTIVE] ?? false;
        $agePolicyActive = isset($this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE]) ? 0 !== $this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE] : 0;
        $cancellationRightPolicyActive = $this->moduleVars[LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE] ?? false;
        $tradeConditionsActive = $this->moduleVars[LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE] ?? false;

        return [
            'termsOfUse'              => $termsOfUseActive,
            'privacyPolicy'           => $privacyPolicyActive,
            'agePolicy'               => $agePolicyActive,
            'cancellationRightPolicy' => $cancellationRightPolicyActive,
            'tradeConditions'         => $tradeConditionsActive,
        ];
    }

    /**
     * Helper method to determine acceptance / confirmation states for current user.
     */
    private function determineAcceptanceState(int $uid = null, string $modVarName = ''): ?string
    {
        $acceptanceState = null;

        if (null !== $uid && !empty($uid) && is_numeric($uid) && $uid > 0) {
            if ($uid > Constant::USER_ID_ADMIN) {
                /** @var UserEntity $user */
                $user = $this->userRepository->find($uid);
                $acceptanceState = $user->getAttributes()->containsKey($modVarName) ? $user->getAttributeValue($modVarName) : null;
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
            'termsOfUse'              => $termsOfUseAccepted,
            'privacyPolicy'           => $privacyPolicyAccepted,
            'agePolicy'               => $agePolicyConfirmed,
            'cancellationRightPolicy' => $cancellationRightPolicyAccepted,
            'tradeConditions'         => $tradeConditionsAccepted,
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
            'termsOfUse'              => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalModule::termsOfUse', '::', ACCESS_MODERATE),
            'privacyPolicy'           => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalModule::privacyPolicy', '::', ACCESS_MODERATE),
            'agePolicy'               => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalModule::agePolicy', '::', ACCESS_MODERATE),
            'cancellationRightPolicy' => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalModule::cancellationRightPolicy', '::', ACCESS_MODERATE),
            'tradeConditions'         => $isCurrentUser ? true : $this->permissionApi->hasPermission('ZikulaLegalModule::tradeConditions', '::', ACCESS_MODERATE),
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
            'termsOfUse'              => $this->permissionApi->hasPermission('ZikulaLegalModule::termsOfUse', '::', ACCESS_EDIT),
            'privacyPolicy'           => $this->permissionApi->hasPermission('ZikulaLegalModule::privacyPolicy', '::', ACCESS_EDIT),
            'agePolicy'               => $this->permissionApi->hasPermission('ZikulaLegalModule::agePolicy', '::', ACCESS_EDIT),
            'cancellationRightPolicy' => $this->permissionApi->hasPermission('ZikulaLegalModule::cancellationRightPolicy', '::', ACCESS_EDIT),
            'tradeConditions'         => $this->permissionApi->hasPermission('ZikulaLegalModule::tradeConditions', '::', ACCESS_EDIT),
        ];
    }
}
