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
     * Module variable key for the flag controlling whether graphics are displayed on the account page or not.
     */
    const MODVAR_ACCOUNT_DISPLAY_GRAPHICS = 'accountdisplaygraphics';

    /**
     * Default value for the flag controlling whether graphics are displayed on the account page or not.
     */
    const DEFAULT_ACCOUNT_DISPLAY_GRAPHICS = true;

    /**
     * Module variable key for the number of items to display on the account page.
     */
    const MODVAR_ACCOUNT_ITEMS_PER_PAGE = 'accountitemsperpage';

    /**
     * Default value for the number of items to display on the account page.
     */
    const DEFAULT_ACCOUNT_ITEMS_PER_PAGE = 25;

    /**
     * Module variable key for the number of items per row to display on the account page.
     */
    const MODVAR_ACCOUNT_ITEMS_PER_ROW = 'accountitemsperrow';

    /**
     * Default value for the number of items per row to display on the account page.
     */
    const DEFAULT_ACCOUNT_ITEMS_PER_ROW = 5;

    /**
     * Module variable key for the account page image path.
     */
    const MODVAR_ACCOUNT_PAGE_IMAGE_PATH = 'userimg';

    /**
     * Default value for the account page image path.
     */
    const DEFAULT_ACCOUNT_PAGE_IMAGE_PATH = 'images/menu';

    /**
     * Module variable key for the guest account (anonymous account) display name.
     */
    const MODVAR_ANONYMOUS_DISPLAY_NAME = 'anonymous';

    /**
     * Module variable key for the avatar image path.
     */
    const MODVAR_AVATAR_IMAGE_PATH = 'avatarpath';

    /**
     * Default value for the avatar image path.
     */
    const DEFAULT_AVATAR_IMAGE_PATH = 'images/avatar';

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
     * Module variable key for the flag indicating whether gravatars are allowed or not.
     */
    const MODVAR_GRAVATARS_ENABLED = 'allowgravatars';

    /**
     * Default value for the flag indicating whether gravatars are allowed or not.
     */
    const DEFAULT_GRAVATARS_ENABLED = true;

    /**
     * Module variable key for the file name containing the generic gravatar image.
     */
    const MODVAR_GRAVATAR_IMAGE = 'gravatarimage';

    /**
     * Default value for the file name containing the generic gravatar image.
     */
    const DEFAULT_GRAVATAR_IMAGE = 'gravatar.gif';

    /**
     * Module variable key for the hash method used for hashing passwords.
     */
    const MODVAR_HASH_METHOD = 'hash_method';

    /**
     * Default value for the hash method used for hashing passwords.
     */
    const DEFAULT_HASH_METHOD = 'sha256';

    /**
     * Module variable key for the number of items (e.g., user account records) to display per list "page."
     */
    const MODVAR_ITEMS_PER_PAGE = 'itemsperpage';

    /**
     * Default value for the number of items (e.g., user account records) to display per list "page."
     */
    const DEFAULT_ITEMS_PER_PAGE = 25;

    /**
     * Module variable key for the flag indicating whether the pending approval status is displayed on a failed log-in attempt.
     */
    const MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS = 'login_displayapproval';

    /**
     * Default value for the flag indicating whether the pending approval status is displayed on a failed log-in attempt.
     */
    const DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS = false;

    /**
     * Module variable key for the flag indicating whether the pending delete status is displayed on a failed log-in attempt.
     */
    const MODVAR_LOGIN_DISPLAY_DELETE_STATUS = 'login_displaydelete';

    /**
     * Default value for the flag indicating whether the pending delete status is displayed on a failed log-in attempt.
     */
    const DEFAULT_LOGIN_DISPLAY_DELETE_STATUS = false;

    /**
     * Module variable key for the flag indicating whether the inactive status is displayed on a failed log-in attempt.
     */
    const MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS = 'login_displayinactive';

    /**
     * Default value for the flag indicating whether the inactive status is displayed on a failed log-in attempt.
     */
    const DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS = false;

    /**
     * Module variable key for the flag indicating whether the pending e-mail verification status is displayed on a failed log-in attempt.
     */
    const MODVAR_LOGIN_DISPLAY_VERIFY_STATUS = 'login_displayverify';

    /**
     * Default value for the flag indicating whether the pending e-mail verification status is displayed on a failed log-in attempt.
     */
    const DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS = false;

    /**
     * Module variable key for the enabled log-in option (for the Users module).
     */
    const MODVAR_LOGIN_METHOD = 'loginviaoption';

    /**
     * Default value for the enabled log-in option (for the Users module).
     */
    const DEFAULT_LOGIN_METHOD = Users_Constant::LOGIN_METHOD_UNAME;

    /**
     * Module variable key for the flag indicating whether WCAG-compliant log-ins should be used (redirect), or not (meta refresh).
     */
    const MODVAR_LOGIN_WCAG_COMPLIANT = 'login_redirect';

    /**
     * Default value for the flag indicating whether WCAG-compliant log-ins should be used (redirect), or not (meta refresh).
     */
    const DEFAULT_LOGIN_WCAG_COMPLIANT = true;

    /**
     * Module variable key for the flag indicating whether the Users module manages the user's e-mail address or not.
     */
    const MODVAR_MANAGE_EMAIL_ADDRESS = 'changeemail';

    /**
     * Default value for the flag indicating whether the Users module manages the user's e-mail address or not.
     */
    const DEFAULT_MANAGE_EMAIL_ADDRESS = true;

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
     * Module variable key for the admin notification e-mail address.
     */
    const MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL = 'reg_notifyemail';

    /**
     * Module variable key for the anti-spam registration question answer text.
     */
    const MODVAR_REGISTRATION_ANTISPAM_ANSWER = 'reg_answer';

    /**
     * Module variable key for the anti-spam registration question text.
     */
    const MODVAR_REGISTRATION_ANTISPAM_QUESTION = 'reg_question';

    /**
     * Module variable key for the flag indicating whether new registrations require approval or not.
     */
    const MODVAR_REGISTRATION_APPROVAL_REQUIRED = 'moderation';

    /**
     * Default value for the flag indicating whether new registrations require approval or not.
     */
    const DEFAULT_REGISTRATION_APPROVAL_REQUIRED = false;

    /**
     * Module variable key for the code indicating the approval/verification sequencing when both are enabled.
     */
    const MODVAR_REGISTRATION_APPROVAL_SEQUENCE = 'moderation_order';

    /**
     * Default value for the code indicating the approval/verification sequencing when both are enabled.
     */
    const DEFAULT_REGISTRATION_APPROVAL_SEQUENCE = Users_Constant::APPROVAL_BEFORE;
    
    /**
     * Module variable key for the flag indicating when a new user registers, should the user be automatically logged in 
     * if admin approval (moderation) and e-mail verification are not required?
     */
    const MODVAR_REGISTRATION_AUTO_LOGIN = 'reg_autologin';
    
    /**
     * Default value for MODVAR_REGISTRATION_AUTO_LOGIN; false == no auto log-in
     */
    const DEFAULT_REGISTRATION_AUTO_LOGIN = false;

    /**
     * Module variable key for the registration disabled reason text.
     */
    const MODVAR_REGISTRATION_DISABLED_REASON = 'reg_noregreasons';

    /**
     * Module variable key for the flag enabling or disabling registration.
     */
    const MODVAR_REGISTRATION_ENABLED = 'reg_allowreg';

    /**
     * Default value for the flag enabling or disabling registration.
     */
    const DEFAULT_REGISTRATION_ENABLED = true;

    /**
     * Module variable key for the number of days until a new registration expires if the e-mail address is not verified.
     */
    const MODVAR_EXPIRE_DAYS_REGISTRATION = 'reg_expiredays';

    /**
     * Default value for the number of days until a new registration expires if the e-mail address is not verified.
     */
    const DEFAULT_EXPIRE_DAYS_REGISTRATION = 0;

    /**
     * Module variable key for the comma-separated list of illegal user agent string fragments.
     */
    const MODVAR_REGISTRATION_ILLEGAL_AGENTS = 'reg_Illegaluseragents';

    /**
     * Module variable key for the comma-separated list of illegal e-mail address domains.
     */
    const MODVAR_REGISTRATION_ILLEGAL_DOMAINS = 'reg_Illegaldomains';

    /**
     * Module variable key for the comma-separated list of illegal user names.
     */
    const MODVAR_REGISTRATION_ILLEGAL_UNAMES = 'reg_Illegalusername';

    /**
     * Module variable key for the flag indicating whether newly registered e-mail addresses must be verified or not.
     */
    const MODVAR_REGISTRATION_VERIFICATION_MODE = 'reg_verifyemail';

    /**
     * Default value for the flag indicating whether newly registered e-mail addresses must be verified or not.
     */
    const DEFAULT_REGISTRATION_VERIFICATION_MODE = Users_Constant::VERIFY_USERPWD;

    /**
     * Module variable key for the flag indicating whether newly registered e-mail addresses must be unique within the system or not.
     */
    const MODVAR_REQUIRE_UNIQUE_EMAIL = 'reg_uniemail';

    /**
     * Default value for the flag indicating whether newly registered e-mail addresses must be unique within the system or not.
     */
    const DEFAULT_REQUIRE_UNIQUE_EMAIL = true;
    
    /**
     * The PCRE regular expression fragment used to validate user names.
     */
    const UNAME_VALIDATION_PATTERN = '[\p{L}\p{N}_\.\-]+';
    
    /**
     * The maximum length of a user name, used for validation.
     */
    const UNAME_VALIDATION_MAX_LENGTH = 25;
    
    /**
     * The PCRE regular expression fragment used to validate e-mail addresses.
     */
    const EMAIL_VALIDATION_PATTERN = '(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}';
    
    /**
     * The PCRE regular expression fragment used to validate e-mail address domains.
     */
    const EMAIL_DOMAIN_VALIDATION_PATTERN = '(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}';

}
