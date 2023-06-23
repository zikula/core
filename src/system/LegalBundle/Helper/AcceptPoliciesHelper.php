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

/**
 * Helper class to process acceptance of policies.
 */
class AcceptPoliciesHelper
{
    public function __construct(private readonly array $legalConfig)
    {
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
}
