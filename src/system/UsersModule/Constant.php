<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule
{
    /**
     * Users module-wide constants.
     * Add only constants here. No variables, functions, or other elements.
     * Constants used for specific classes can be defined in those classes. The constants in this class are module-wide
     * constants.
     */
    class Constant
    {
        /**
         * The name of the module.
         */
        const MODNAME = 'ZikulaUsersModule';
        /**
         * The namespace of the module to use for session variables.
         */
        const SESSION_VAR_NAMESPACE = 'Zikula_Module_UsersModule';
        /**
         * This key is used to 'disguise' the purpose of passing the UID in the session.
         */
        const FORCE_PASSWORD_SESSION_UID_KEY = 'l56F2fe7ZBbLm6ruQhgU';
        /**
         * This key is used to define an attribute.
         */
        const AUTHENTICATION_METHOD_ATTRIBUTE_KEY = 'authenticationMethod';
        /**
         * Pending registration (not able to log in).
         * Moderation and/or e-mail verification are in use in the registration process, and one or more of the required steps has not yet
         * been completed.
         */
        const ACTIVATED_PENDING_REG = -32768;
        /**
         * User 'activated' state of 'inactive'--not able to log in.
         * This state may be set by the site administrator to prevent any attempt to log in with this account.
         */
        const ACTIVATED_INACTIVE = 0;
        /**
         * User 'activated' state of 'active'--able to log in.
         */
        const ACTIVATED_ACTIVE = 1;
        /**
         * User 'activated' state of 'marked for deletion'--soft delete (FUTURE USE)
         * Similar to inactive, but with the expectation that the account could be removed at any time. This state can also be used to
         * simulate deletion without actually deleting the account.
         */
        const ACTIVATED_PENDING_DELETE = 16384;
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
        const DEFAULT_GRAVATAR_IMAGE = 'gravatar.jpg';

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
         * Module variable key for the admin notification e-mail address.
         */
        const MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL = 'reg_notifyemail';
        /**
         * Module variable key for the flag indicating whether new registrations require approval or not.
         */
        const MODVAR_REGISTRATION_APPROVAL_REQUIRED = 'moderation';
        /**
         * Default value for the flag indicating whether new registrations require approval or not.
         */
        const DEFAULT_REGISTRATION_APPROVAL_REQUIRED = false;
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
         * The PCRE regular expression fragment used to validate user names.
         * 17 June 2016 - CAH - As of ZikulaUsersModule 3.0, spaces are allowed and the field is not restricted to lowercase
         */
        const UNAME_VALIDATION_PATTERN = '[\\p{L}\\p{N}_\\.\\-\\s?]+';
        /**
         * The maximum length of a user name, used for validation.
         */
        const UNAME_VALIDATION_MAX_LENGTH = 25;
        /**
         * The PCRE regular expression fragment used to validate e-mail address domains.
         */
        const EMAIL_DOMAIN_VALIDATION_PATTERN = '(?:[^\\s\\000-\\037\\177\\(\\)<>@,;:\\\\"\\[\\]]\\.?)+\\.[a-z]{2,6}';
    }
}
