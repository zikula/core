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

    /**
     * The string identifying that a user can user the native_uname authentication method.
     */
    const AUTHENTICATION_METHOD_UNAME = 'native_uname';

    /**
     * The string identifying that a user can user the native_email authentication method.
     */
    const AUTHENTICATION_METHOD_EMAIL = 'native_email';

    /**
     * Module variable key for the number of days before a change of e-mail request is canceled.
     */
    const MODVAR_EXPIRE_DAYS_CHANGE_EMAIL = 'chgemail_expiredays';

    /**
     * Default value for the number of days before a change of e-mail request is canceled.
     */
    const DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL = 0;

    /**
     * Module variable key for the number of days before a change of password request is canceled.
     */
    const MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD = 'chgpass_expiredays';

    /**
     * Default value for the number of days before a change of password request is canceled.
     */
    const DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD = 0;

    /**
     * Module variable key for the anti-spam registration question answer text.
     */
    const MODVAR_REGISTRATION_ANTISPAM_ANSWER = 'reg_answer';

    /**
     * Module variable key for the anti-spam registration question text.
     */
    const MODVAR_REGISTRATION_ANTISPAM_QUESTION = 'reg_question';

    /**
     * An indicator for the change verification table that the record represents a change of password request.
     */
    const VERIFYCHGTYPE_PWD = 1;

    /**
     * An indicator for the change verification table that the record represents a change of e-mail address request, pending e-mail address verification.
     */
    const VERIFYCHGTYPE_EMAIL = 2;

    /**
     * An indicator for the change verification table that the record represents a registration e-mail verification.
     */
    const VERIFYCHGTYPE_REGEMAIL = 3;
}
