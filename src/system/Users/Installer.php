<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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
 * Provides module installation and upgrade services for the Users module.
 */
class Users_Installer extends Zikula_AbstractInstaller
{
    /**
     * Initialise the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance. This function MUST exist in the pninit file for a module.
     *
     * @return bool True on success, false otherwise.
     */
    public function install()
    {
        if (!DBUtil::createTable('session_info')) {
            return false;
        }

        if (!DBUtil::createTable('users')) {
            return false;
        }

        if (!DBUtil::createTable('users_verifychg')) {
            return false;
        }

        // Set default values and modvars for module
        $this->defaultdata();
        $this->setVars($this->getDefaultModvars());

        // Register persistent event listeners (handlers)
        EventUtil::registerPersistentModuleHandler($this->name, 'get.pending_content', array('Users_Listener_PendingContent', 'pendingContentListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.login.veto', array('Users_Listener_ForcedPasswordChange', 'forcedPasswordChangeListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.logout.succeeded', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.exception', array('Users_Listener_ClearUsersNamespace', 'clearUsersNamespaceListener'));

        // Register persistent hook bundles
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the users module from an older version.
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldVersion Version number string to upgrade from.
     *
     * @return mixed True on success, last valid version string or false if fails.
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '2.2.0':
                // This s the current version: add 2.2.0 --> next when appropriate
        }

        $currentModVars = $this->getVars();
        $defaultModVars = $this->getDefaultModvars();

        // Remove modvars that are no longer defined.
        foreach ($currentModVars as $modVar => $currentValue) {
            if (!array_key_exists($modVar, $defaultModVars)) {
                $this->delVar($modVar);
            }
        }

        // Add modvars that are new to the version
        foreach ($defaultModVars as $modVar => $defaultValue) {
            if (!array_key_exists($modVar, $currentModVars)) {
                $this->setVar($modVar, $defaultValue);
            }
        }

        // Update successful
        return true;
    }

    /**
     * Delete the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance. This function MUST exist in the pninit file for a module.
     *
     * Since the users module should never be deleted we'all always return false here.
     *
     * @return bool false
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Build and return an array of all current module variables, with their default values.
     *
     * @return array An array of all current module variables, with their default values, suitable for {@link setVars()}.
     */
    private function getDefaultModvars()
    {
        return array(
            Users_Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS              => Users_Constant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
            Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE                => Users_Constant::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
            Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW                 => Users_Constant::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
            Users_Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH               => Users_Constant::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
            Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->__(/* Anonymous (guest) account display name */'Guest'),
            Users_Constant::MODVAR_AVATAR_IMAGE_PATH                     => Users_Constant::DEFAULT_AVATAR_IMAGE_PATH,
            Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL              => Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD           => Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            Users_Constant::MODVAR_EXPIRE_DAYS_REGISTRATION              => Users_Constant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            Users_Constant::MODVAR_GRAVATARS_ENABLED                     => Users_Constant::DEFAULT_GRAVATARS_ENABLED,
            Users_Constant::MODVAR_GRAVATAR_IMAGE                        => Users_Constant::DEFAULT_GRAVATAR_IMAGE,
            Users_Constant::MODVAR_HASH_METHOD                           => Users_Constant::DEFAULT_HASH_METHOD,
            Users_Constant::MODVAR_ITEMS_PER_PAGE                        => Users_Constant::DEFAULT_ITEMS_PER_PAGE,
            Users_Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => Users_Constant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            Users_Constant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => Users_Constant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            Users_Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => Users_Constant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            Users_Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => Users_Constant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            Users_Constant::MODVAR_LOGIN_METHOD                          => Users_Constant::DEFAULT_LOGIN_METHOD,
            Users_Constant::MODVAR_LOGIN_WCAG_COMPLIANT                  => Users_Constant::DEFAULT_LOGIN_WCAG_COMPLIANT,
            Users_Constant::MODVAR_MANAGE_EMAIL_ADDRESS                  => Users_Constant::DEFAULT_MANAGE_EMAIL_ADDRESS,
            Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH               => Users_Constant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            Users_Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED       => Users_Constant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            Users_Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION        => '',
            Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER          => '',
            Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => Users_Constant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE        => Users_Constant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
            Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN               => Users_Constant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            Users_Constant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->__(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            Users_Constant::MODVAR_REGISTRATION_ENABLED                  => Users_Constant::DEFAULT_REGISTRATION_ENABLED,
            Users_Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            Users_Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->__(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
            Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE        => Users_Constant::DEFAULT_REGISTRATION_VERIFICATION_MODE,
            Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL                  => Users_Constant::DEFAULT_REQUIRE_UNIQUE_EMAIL,
        );
    }

    /**
     * Create the default data for the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     *
     * @return void
     */
    private function defaultdata()
    {
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(Users_Constant::DATETIME_FORMAT);

        // Anonymous
        $record = array(
            'uid'           => 1,
            'uname'         => 'guest',
            'email'         => '',
            'pass'          => '',
            'passreminder'  => '',
            'activated'     => Users_Constant::ACTIVATED_ACTIVE,
            'approved_date' => '1970-01-01 00:00:00',
            'approved_by'   => 0,
            'user_regdate'  => '1970-01-01 00:00:00',
            'lastlogin'     => '1970-01-01 00:00:00',
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        DBUtil::insertObject($record, 'users', 'uid', true);

        // Admin
        $record = array(
            'uid'           => 2,
            'uname'         => 'admin',
            'email'         => '',
            'pass'          => '1$$dc647eb65e6711e155375218212b3964',
            'passreminder'  => '',
            'activated'     => Users_Constant::ACTIVATED_ACTIVE,
            'approved_date' => $nowUTCStr,
            'approved_by'   => 2,
            'user_regdate'  => $nowUTCStr,
            'lastlogin'     => '1970-01-01 00:00:00',
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        DBUtil::insertObject($record, 'users', 'uid', true);
    }
}
