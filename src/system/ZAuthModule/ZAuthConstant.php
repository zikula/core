<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule;

class ZAuthConstant
{
    /**
     * Module variable key for the minimum password length.
     */
    const MODVAR_PASSWORD_MINIMUM_LENGTH = 'minpass';

    /**
     * Default value for the minimum password length.
     */
    const DEFAULT_PASSWORD_MINIMUM_LENGTH = 5;

    /**
     * Module variable key for the flag indicating whether the password strength meter should be enabled or not.
     */
    const MODVAR_PASSWORD_STRENGTH_METER_ENABLED = 'use_password_strength_meter';

    /**
     * Default value for the flag indicating whether the password strength meter should be enabled or not.
     */
    const DEFAULT_PASSWORD_STRENGTH_METER_ENABLED = false;

    /**
     * Module variable key for the flag indicating whether the password reminder should be enabled.
     */
    const MODVAR_PASSWORD_REMINDER_ENABLED = 'password_reminder_enabled';

    /**
     * Default value for the flag indicating whether the password reminder should be enabled.
     */
    const DEFAULT_PASSWORD_REMINDER_ENABLED = false;

    /**
     * Module variable key for the flag indicating whether the password reminder should be mandatory or not.
     */
    const MODVAR_PASSWORD_REMINDER_MANDATORY = 'password_reminder_mandatory';

    /**
     * Default value for the flag indicating whether the password reminder should be mandatory or not.
     */
    const DEFAULT_PASSWORD_REMINDER_MANDATORY = true;

    /**
     * Module variable key for the hash method used for hashing passwords.
     */
    const MODVAR_HASH_METHOD = 'hash_method';

    /**
     * Default value for the hash method used for hashing passwords.
     */
    const DEFAULT_HASH_METHOD = 'sha256';

    /**
     * Module variable key for the status of requirement for email verification.
     */
    const MODVAR_EMAIL_VERIFICATION_REQUIRED = 'email_verification_required';

    /**
     * Default value for the status of requirement for email verification.
     */
    const DEFAULT_EMAIL_VERIFICATION_REQUIRED = true;

    /**
     * The string identifying that a user can user either the native_uname or native_email authentication methods.
     */
    const AUTHENTICATION_METHOD_EITHER = 'native_either';
}
