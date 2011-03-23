<?php
/**
 * Copyright 2011 Zikula Foundation.
 * 
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 * 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 * 
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Users module-wide constants.
 * 
 * Add only constants here. No variables, functions, or other elements.
 * 
 * Constants used for specific classes can be defined in those classes. The constants in this class are module-wide
 * constants.
 */
class Users_Constant
{
    /**
     * The name of the module.
     */
    const MODNAME = 'Users';

    /**
     * The identifier for the 'authentication' capability.
     */
    const CAPABILITY_AUTHENTICATION = 'authentication';

    /**
     * Allow users to identify themselves during login with only their user name (does not affect alternate authentication methods).
     */
    const LOGIN_METHOD_UNAME = 0;

    /**
     * Allow users to identify themselves during login with only their e-mail address (does not affect alternate authentication methods).
     */
    const LOGIN_METHOD_EMAIL = 1;

    /**
     * Allow users to identify themselves during login with either their user name or their e-mail address (does not affect alternate authentication methods).
     */
    const LOGIN_METHOD_ANY = 2;

    /**
     * Pending registration (not able to log in).
     *
     * Moderation and/or e-mail verification are in use in the registration process, and one or more of the required steps has not yet
     * been completed.
     */
    const ACTIVATED_PENDING_REG = -32768;

    /**
     * User 'activated' state of 'inactive'--not able to log in.
     *
     * This state may be set by the site administrator to prevent any attempt to log in with this account.
     */
    const ACTIVATED_INACTIVE = 0;

    /**
     * User 'activated' state of 'active'--able to log in.
     */
    const ACTIVATED_ACTIVE = 1;

    /**
     * User 'activated' state of 'marked for deletion'--soft delete (FUTURE USE)
     *
     * Similar to inactive, but with the expectation that the account could be removed at any time. This state can also be used to
     * simulate deletion without actually deleting the account.
     */
    const ACTIVATED_PENDING_DELETE = 16384;

    /**
     * A user's e-mail address is not verified during the registration process, and a user selects his own password.
     */
    const VERIFY_NO = 0;

    /**
     * A user's e-mail address is verified by sending him a system-generated password directly to the e-mail address provided; the user does not select his initial password.
     *
     * NOTE: Use of system-generated passwords is deprecated due to security concerns when sending passwords via e-mail.
     *
     * @deprecated since 1.3.0
     */
    const VERIFY_SYSTEMPWD = 1;

    /**
     * A user's e-mail address is verified by sending him a unique verification code to the e-mail address provided; the user selects his own password during registration.
     */
    const VERIFY_USERPWD = 2;

    /**
     * A moderator must approve a registration application prior to any e-mail verification process is initiated.
     */
    const APPROVAL_BEFORE = 0;

    /**
     * The user must complete the e-mail verification process first, then the moderator must approve the account.
     *
     * If the user is created directly by the administrator, then the need for verification can be overridden.
     */
    const APPROVAL_AFTER = 1;

    /**
     * The administrator's approval of a registration application and the user's verification of his e-mail address can happen in any order.
     */
    const APPROVAL_ANY = 2;

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

    /**
     * Pseudo-password used as a marker for account records registered with an authentication method other than one from the Users module.
     */
    const PWD_NO_USERS_AUTHENTICATION = 'NO_USERS_AUTHENTICATION';

    /**
     * Default salt delimeter character.
     */
    const SALT_DELIM = '$';

    /**
     * Date-time format for use with DateTime#format(), date() and gmdate() for database storage.
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * A date/time indicating that a change request verification has expired.
     */
    const EXPIRED = '1901-12-21 20:45:52';

    /**
     *
     */
    const MODVAR_ACCOUNT_DISPLAY_GRAPHICS = 'accountdisplaygraphics';

    /**
     *
     */
    const DEFAULT_ACCOUNT_DISPLAY_GRAPHICS = true;

    /**
     *
     */
    const MODVAR_ACCOUNT_ITEMS_PER_PAGE = 'accountitemsperpage';

    /**
     *
     */
    const DEFAULT_ACCOUNT_ITEMS_PER_PAGE = 25;

    /**
     *
     */
    const MODVAR_ACCOUNT_ITEMS_PER_ROW = 'accountitemsperrow';

    /**
     *
     */
    const DEFAULT_ACCOUNT_ITEMS_PER_ROW = 5;

    /**
     *
     */
    const MODVAR_ACCOUNT_PAGE_IMAGE_PATH = 'userimg';

    /**
     *
     */
    const DEFAULT_ACCOUNT_PAGE_IMAGE_PATH = 'images/menu';

    /**
     *
     */
    const MODVAR_ANONYMOUS_DISPLAY_NAME = 'anonymous';

    /**
     *
     */
    const DEFAULT_ANONYMOUS_DISPLAY_NAME = 'Guest';

    /**
     *
     */
    const MODVAR_AVATAR_IMAGE_PATH = 'avatarpath';

    /**
     *
     */
    const DEFAULT_AVATAR_IMAGE_PATH = 'images/avatar';

    /**
     *
     */
    const MODVAR_EXPIRE_DAYS_CHANGE_EMAIL = 'chgemail_expiredays';

    /**
     *
     */
    const DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL = 0;

    /**
     *
     */
    const MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD = 'chgpass_expiredays';

    /**
     *
     */
    const DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD = 0;

    /**
     *
     */
    const MODVAR_GRAVATARS_ENABLED = 'allowgravatars';

    /**
     *
     */
    const DEFAULT_GRAVATARS_ENABLED = true;

    /**
     *
     */
    const MODVAR_GRAVATAR_IMAGE = 'gravatarimage';

    /**
     *
     */
    const DEFAULT_GRAVATAR_IMAGE = 'gravatar.gif';

    /**
     *
     */
    const MODVAR_HASH_METHOD = 'hash_method';

    /**
     *
     */
    const DEFAULT_HASH_METHOD = 'sha256';

    /**
     * The number of items (e.g., user account records) to display per list "page."
     */
    const MODVAR_ITEMS_PER_PAGE = 'itemsperpage';

    /**
     *
     */
    const DEFAULT_ITEMS_PER_PAGE = 25;

    /**
     *
     */
    const MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS = 'login_displayapproval';

    /**
     *
     */
    const DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS = false;

    /**
     *
     */
    const MODVAR_LOGIN_DISPLAY_DELETE_STATUS = 'login_displaydelete';

    /**
     *
     */
    const DEFAULT_LOGIN_DISPLAY_DELETE_STATUS = false;

    /**
     *
     */
    const MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS = 'login_displayinactive';

    /**
     *
     */
    const DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS = false;

    /**
     *
     */
    const MODVAR_LOGIN_DISPLAY_VERIFY_STATUS = 'login_displayverify';

    /**
     *
     */
    const DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS = false;

    /**
     *
     */
    const MODVAR_LOGIN_METHOD = 'loginviaoption';

    /**
     *
     */
    const DEFAULT_LOGIN_METHOD = Users_Constant::LOGIN_METHOD_UNAME;

    /**
     *
     */
    const MODVAR_LOGIN_WCAG_COMPLIANT = 'login_redirect';

    /**
     *
     */
    const DEFAULT_LOGIN_WCAG_COMPLIANT = true;

    /**
     *
     */
    const MODVAR_MANAGE_EMAIL_ADDRESS = 'changeemail';

    /**
     *
     */
    const DEFAULT_MANAGE_EMAIL_ADDRESS = true;

    /**
     *
     */
    const MODVAR_PASSWORD_MINIMUM_LENGTH = 'minpass';

    /**
     *
     */
    const DEFAULT_PASSWORD_MINIMUM_LENGTH = 5;

    /**
     *
     */
    const MODVAR_PASSWORD_STRENGTH_METER_ENABLED = 'use_password_strength_meter';

    /**
     *
     */
    const DEFAULT_PASSWORD_STRENGTH_METER_ENABLED = false;

    /**
     *
     */
    const MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL = 'reg_notifyemail';

    /**
     *
     */
    const MODVAR_REGISTRATION_ANTISPAM_ANSWER = 'reg_answer';

    /**
     *
     */
    const MODVAR_REGISTRATION_ANTISPAM_QUESTION = 'reg_question';

    /**
     *
     */
    const MODVAR_REGISTRATION_APPROVAL_REQUIRED = 'moderation';

    /**
     *
     */
    const DEFAULT_REGISTRATION_APPROVAL_REQUIRED = false;

    /**
     *
     */
    const MODVAR_REGISTRATION_APPROVAL_SEQUENCE = 'moderation_order';

    /**
     *
     */
    const DEFAULT_REGISTRATION_APPROVAL_SEQUENCE = Users_Constant::APPROVAL_BEFORE;

    /**
     *
     */
    const MODVAR_REGISTRATION_DISABLED_REASON = 'reg_noregreasons';

    /**
     *
     */
    const MODVAR_REGISTRATION_ENABLED = 'reg_allowreg';

    /**
     *
     */
    const DEFAULT_REGISTRATION_ENABLED = true;

    /**
     *
     */
    const MODVAR_EXPIRE_DAYS_REGISTRATION = 'reg_expiredays';

    /**
     *
     */
    const DEFAULT_EXPIRE_DAYS_REGISTRATION = 0;

    /**
     *
     */
    const MODVAR_REGISTRATION_ILLEGAL_AGENTS = 'reg_Illegaluseragents';

    /**
     *
     */
    const MODVAR_REGISTRATION_ILLEGAL_DOMAINS = 'reg_Illegaldomains';

    /**
     *
     */
    const MODVAR_REGISTRATION_ILLEGAL_UNAMES = 'reg_Illegalusername';

    /**
     *
     */
    const MODVAR_REGISTRATION_VERIFICATION_MODE = 'reg_verifyemail';

    /**
     *
     */
    const DEFAULT_REGISTRATION_VERIFICATION_MODE = Users_Constant::VERIFY_USERPWD;

    /**
     *
     */
    const MODVAR_REQUIRE_UNIQUE_EMAIL = 'reg_uniemail';

    /**
     *
     */
    const DEFAULT_REQUIRE_UNIQUE_EMAIL = true;
    
    /**
     * 
     */
    const UNAME_VALIDATION_PATTERN = '[\p{L}\p{N}_\.\-]+';
    
    /**
     * 
     */
    const UNAME_VALIDATION_MAX_LENGTH = 25;
    
    const EMAIL_VALIDATION_PATTERN = '(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}';
    
    const EMAIL_DOMAIN_VALIDATION_PATTERN = '(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}';

}
