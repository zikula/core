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

namespace Zikula\LegalModule;

/**
 * Module-wide constants for the Legal module.
 *
 * NOTE: Do not define anything other than constants in this class!
 */
class Constant
{
    /**
     * The official internal module name.
     *
     * @var string
     */
    public const MODNAME = 'ZikulaLegalModule';

    /**
     * The form data prefix
     */
    public const FORM_BLOCK_PREFIX = 'zikulalegalmodule_policy';

    /**
     * This key is used to 'disguise' the purpose of passing the UID in the session.
     */
    public const FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY = 'kjh4kjgsdkjyf76r44hf9';

    /**
     * The module variable name indicating that the legal notice is active.
     *
     * @var string
     */
    public const MODVAR_LEGALNOTICE_ACTIVE = 'legalNoticeActive';

    /**
     * The module variable name indicating that the terms of use is active.
     *
     * @var string
     */
    public const MODVAR_TERMS_ACTIVE = 'termsOfUseActive';

    /**
     * The module variable name indicating that the privacy policy is active.
     *
     * @var string
     */
    public const MODVAR_PRIVACY_ACTIVE = 'privacyPolicyActive';

    /**
     * The module variable name indicating that the accessibility statement is active.
     *
     * @var string
     */
    public const MODVAR_ACCESSIBILITY_ACTIVE = 'accessibilityStatementActive';

    /**
     * The module variable name indicating that the trade conditions page is active.
     *
     * @var string
     */
    public const MODVAR_TRADECONDITIONS_ACTIVE = 'tradeConditionsActive';

    /**
     * The module variable name indicating that the cancellation right policy page is active.
     *
     * @var string
     */
    public const MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE = 'cancellationRightPolicyActive';

    /**
     * The module variable name storing an optional url for the legal notice.
     *
     * @var string
     */
    public const MODVAR_LEGALNOTICE_URL = 'legalNoticeUrl';

    /**
     * The module variable name storing an optional url for the terms of use.
     *
     * @var string
     */
    public const MODVAR_TERMS_URL = 'termsOfUseUrl';

    /**
     * The module variable name storing an optional url for the privacy policy.
     *
     * @var string
     */
    public const MODVAR_PRIVACY_URL = 'privacyPolicyUrl';

    /**
     * The module variable name storing an optional url for the accessibility guidelines.
     *
     * @var string
     */
    public const MODVAR_ACCESSIBILITY_URL = 'accessibilityStatementUrl';

    /**
     * The module variable name storing an optional url for the trade conditions.
     *
     * @var string
     */
    public const MODVAR_TRADECONDITIONS_URL = 'tradeConditionsUrl';

    /**
     * The module variable name storing an optional url for the cancellation right policy.
     *
     * @var string
     */
    public const MODVAR_CANCELLATIONRIGHTPOLICY_URL = 'cancellationRightPolicyUrl';

    /**
     * The module variable containing the minimum age.
     *
     * @var string
     */
    public const MODVAR_MINIMUM_AGE = 'minimumAge';

    /**
     * The module variable indicating that the EU cookie law complaince option is enabled.
     *
     * @var string
     */
    public const MODVAR_EUCOOKIE = 'eucookie';

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

    /**
     * Users account record attribute key for eu cookie acceptance.
     *
     * @var string
     */
    public const ATTRIBUTE_EUCOOKIE_ACCEPTED = '_Legal_euCookieConfirmed';
}
