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

namespace Zikula\ZAuthBundle;

class ZAuthConstant
{
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

    /**
     * Key used for storing email verification state in session.
     */
    public const SESSION_EMAIL_VERIFICATION_STATE = 'email_verification_state';
}
