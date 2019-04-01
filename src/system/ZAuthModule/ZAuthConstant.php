<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
    public const MODVAR_PASSWORD_MINIMUM_LENGTH = 'minpass';

    /**
     * Default value for the minimum password length.
     */
    public const DEFAULT_PASSWORD_MINIMUM_LENGTH = 5;

    /**
     * Module variable key for the flag indicating whether the password strength meter should be enabled or not.
     */
    public const MODVAR_PASSWORD_STRENGTH_METER_ENABLED = 'use_password_strength_meter';

    /**
     * Default value for the flag indicating whether the password strength meter should be enabled or not.
     */
    public const DEFAULT_PASSWORD_STRENGTH_METER_ENABLED = false;

    /**
     * Module variable key for the hash method used for hashing passwords.
     */
    public const MODVAR_HASH_METHOD = 'hash_method';

    /**
     * Default value for the hash method used for hashing passwords.
     */
    public const DEFAULT_HASH_METHOD = 'sha256';

    /**
     * Module variable key for the status of requirement for email verification.
     */
    public const MODVAR_EMAIL_VERIFICATION_REQUIRED = 'email_verification_required';

    /**
     * Default value for the status of requirement for email verification.
     */
    public const DEFAULT_EMAIL_VERIFICATION_REQUIRED = true;

    /**
     * The string identifying that a user can user either the native_uname or native_email authentication methods.
     */
    public const AUTHENTICATION_METHOD_EITHER = 'native_either';

    /**
     * The string identifying that a user can user the native_uname authentication method.
     */
    public const AUTHENTICATION_METHOD_UNAME = 'native_uname';

    /**
     * The string identifying that a user can user the native_email authentication method.
     */
    public const AUTHENTICATION_METHOD_EMAIL = 'native_email';

    /**
     * Module variable key for the number of days before a change of e-mail request is canceled.
     */
    public const MODVAR_EXPIRE_DAYS_CHANGE_EMAIL = 'chgemail_expiredays';

    /**
     * Default value for the number of days before a change of e-mail request is canceled.
     */
    public const DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL = 0;

    /**
     * Module variable key for the number of days before a change of password request is canceled.
     */
    public const MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD = 'chgpass_expiredays';

    /**
     * Default value for the number of days before a change of password request is canceled.
     */
    public const DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD = 0;

    /**
     * Module variable key for the number of days until a new registration expires if the e-mail address is not verified.
     */
    public const MODVAR_EXPIRE_DAYS_REGISTRATION = 'reg_expiredays';

    /**
     * Default value for the number of days until a new registration expires if the e-mail address is not verified.
     */
    public const DEFAULT_EXPIRE_DAYS_REGISTRATION = 0;

    /**
     * Module variable key for the anti-spam registration question answer text.
     */
    public const MODVAR_REGISTRATION_ANTISPAM_ANSWER = 'reg_answer';

    /**
     * Module variable key for the anti-spam registration question text.
     */
    public const MODVAR_REGISTRATION_ANTISPAM_QUESTION = 'reg_question';

    /**
     * An indicator for the change verification table that the record represents a change of password request.
     */
    public const VERIFYCHGTYPE_PWD = 1;

    /**
     * An indicator for the change verification table that the record represents a change of e-mail address request, pending e-mail address verification.
     */
    public const VERIFYCHGTYPE_EMAIL = 2;

    /**
     * An indicator for the change verification table that the record represents a registration e-mail verification.
     */
    public const VERIFYCHGTYPE_REGEMAIL = 3;

    /**
     * Key used as a UserAttribute that indicates a user is required to change his password on next login.
     * Set value to (bool) TRUE if change is required. The existence of the key and the value are both tested.
     */
    public const REQUIRE_PASSWORD_CHANGE_KEY = '_Users_mustChangePassword';
}
