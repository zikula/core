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

namespace Zikula\LegalBundle;

/**
 * Bundle-wide constants for the Legal bundle.
 *
 * NOTE: Do not define anything other than constants in this class!
 */
class LegalConstant
{
    /**
     * The form data prefix
     */
    public const FORM_BLOCK_PREFIX = 'zikulalegalbundle_policy';

    /**
     * This key is used to 'disguise' the purpose of passing the UID in the session.
     */
    public const FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY = 'kjh4kjgsdkjyf76r44hf9';

    /**
     * Users account record attribute key for terms of use acceptance.
     *
     * @var string
     */
    public const ATTRIBUTE_TERMSOFUSE_ACCEPTED = '_Legal_termsOfUseAccepted';

    /**
     * Users account record attribute key for terms of use acceptance.
     *
     * @var string
     */
    public const ATTRIBUTE_PRIVACYPOLICY_ACCEPTED = '_Legal_privacyPolicyAccepted';

    /**
     * Users account record attribute key for age policy confirmation.
     *
     * @var string
     */
    public const ATTRIBUTE_AGEPOLICY_CONFIRMED = '_Legal_agePolicyConfirmed';

    /**
     * Users account record attribute key for trade conditions acceptance.
     *
     * @var string
     */
    public const ATTRIBUTE_TRADECONDITIONS_ACCEPTED = '_Legal_tradeConditionsConfirmed';

    /**
     * Users account record attribute key for cancellation right policy acceptance.
     *
     * @var string
     */
    public const ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED = '_Legal_cancellationRightPolicyConfirmed';
}
