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

namespace Zikula\UsersBundle;

/**
 * Users module-wide constants.
 * Add only constants here. No variables, functions, or other elements.
 * Constants used for specific classes can be defined in those classes. The constants in this class are module-wide
 * constants.
 */
class UsersConstant
{
    /**
     * The UID of the 'anonymous' user
     */
    public const USER_ID_ANONYMOUS = 1;

    /**
     * The UID of the default/generated admin user
     */
    public const USER_ID_ADMIN = 2;

    /**
     * This key is used to 'disguise' the purpose of passing the UID in the session.
     */
    public const FORCE_PASSWORD_SESSION_UID_KEY = 'l56F2fe7ZBbLm6ruQhgU';

    /**
     * This key is used to define an attribute.
     */
    public const AUTHENTICATION_METHOD_ATTRIBUTE_KEY = 'authenticationMethod';

    /**
     * Pending registration (not able to log in).
     * Moderation and/or e-mail verification are in use in the registration process, and one or more of the required steps has not yet
     * been completed.
     */
    public const ACTIVATED_PENDING_REG = -32768;

    /**
     * User 'activated' state of 'inactive'--not able to log in.
     * This state may be set by the site administrator to prevent any attempt to log in with this account.
     */
    public const ACTIVATED_INACTIVE = 0;

    /**
     * User 'activated' state of 'active'--able to log in.
     */
    public const ACTIVATED_ACTIVE = 1;

    /**
     * User 'activated' state of 'marked for deletion'--soft delete (FUTURE USE)
     * Similar to inactive, but with the expectation that the account could be removed at any time. This state can also be used to
     * simulate deletion without actually deleting the account.
     */
    public const ACTIVATED_PENDING_DELETE = 16384;

    public const ACTIVATED_OPTIONS = [
        self::ACTIVATED_ACTIVE,
        self::ACTIVATED_INACTIVE,
        self::ACTIVATED_PENDING_DELETE,
        self::ACTIVATED_PENDING_REG
    ];

    /**
     * Default salt delimeter character.
     */
    public const SALT_DELIM = '$';

    /**
     * Date-time format for use with DateTime#format(), date() and gmdate() for database storage.
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * A date/time indicating that a change request verification has expired.
     */
    public const EXPIRED = '1901-12-21 20:45:52';

    /**
     * The PCRE regular expression fragment used to validate user names.
     * 17 June 2016 - CAH - As of ZikulaUsersModule 3.0, spaces are allowed and the field is not restricted to lowercase
     */
    public const UNAME_VALIDATION_PATTERN = '[\\p{L}\\p{N}_\\.\\-\\s?]+';

    /**
     * The maximum length of a user name, used for validation.
     */
    public const UNAME_VALIDATION_MAX_LENGTH = 25;

    /**
     * The PCRE regular expression fragment used to validate e-mail address domains.
     * Note the last part's allowed length is indeed 64 characters (based on RFC 1034), see #3980 for more information.
     */
    public const EMAIL_DOMAIN_VALIDATION_PATTERN = '(?:[^\\s\\000-\\037\\177\\(\\)<>@,;:\\\\"\\[\\]]\\.?)+\\.[a-z]{2,64}';
}
