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

namespace UsersModule;

use DoctrineHelper, EventUtil, HookUtil, System, DataUtil, ModUtil;
use DateTime, DateTimeZone, ServiceUtil;
use UsersModule\Constants as Constant;
use \Zikula\Framework\Exception\FatalException;

/**
 * Provides module installation and upgrade services for the Users module.
 */
class Installer extends \Zikula\Framework\AbstractInstaller
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
        // create the tables
        $classes = array(
            'UsersModule\Entity\User',
            'UsersModule\Entity\UserAttribute',
            'UsersModule\Entity\UserSession',
            'UsersModule\Entity\UserVerification'
        );
        try {
            DoctrineHelper::createSchema($this->entityManager, $classes);
        } catch (\Exception $e) {
            return false;
        }

        // Set default values and modvars for module
        $this->defaultdata();
        $this->setVars($this->getDefaultModvars());

        // Register persistent event listeners (handlers)
        EventUtil::registerPersistentModuleHandler($this->name, 'get.pending_content',
            array('UsersModule\Listener\PendingContentListener', 'pendingContentListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.login.veto',
            array('UsersModule\Listener\ForcedPasswordChangeListener', 'forcedPasswordChangeListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.logout.succeeded',
            array('UsersModule\Listener\ClearUsersNamespace\Listener', 'clearUsersNamespaceListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.exception',
            array('UsersModule\Listener\ClearUsersNamespaceListener', 'clearUsersNamespaceListener'));

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
            case '2.2.1':
                // This is the current version: add 2.2.1 --> next when appropriate
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
            Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS              => Constant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
            Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE                => Constant::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
            Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW                 => Constant::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
            Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH               => Constant::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
            Constant::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->__(/* Anonymous (guest) account display name */'Guest'),
            Constant::MODVAR_AVATAR_IMAGE_PATH                     => Constant::DEFAULT_AVATAR_IMAGE_PATH,
            Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL              => Constant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD           => Constant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            Constant::MODVAR_EXPIRE_DAYS_REGISTRATION              => Constant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            Constant::MODVAR_GRAVATARS_ENABLED                     => Constant::DEFAULT_GRAVATARS_ENABLED,
            Constant::MODVAR_GRAVATAR_IMAGE                        => Constant::DEFAULT_GRAVATAR_IMAGE,
            Constant::MODVAR_HASH_METHOD                           => Constant::DEFAULT_HASH_METHOD,
            Constant::MODVAR_ITEMS_PER_PAGE                        => Constant::DEFAULT_ITEMS_PER_PAGE,
            Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => Constant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            Constant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => Constant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => Constant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => Constant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            Constant::MODVAR_LOGIN_METHOD                          => Constant::DEFAULT_LOGIN_METHOD,
            Constant::MODVAR_LOGIN_WCAG_COMPLIANT                  => Constant::DEFAULT_LOGIN_WCAG_COMPLIANT,
            Constant::MODVAR_MANAGE_EMAIL_ADDRESS                  => Constant::DEFAULT_MANAGE_EMAIL_ADDRESS,
            Constant::MODVAR_PASSWORD_MINIMUM_LENGTH               => Constant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED       => Constant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION        => '',
            Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER          => '',
            Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => Constant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE        => Constant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
            Constant::MODVAR_REGISTRATION_AUTO_LOGIN               => Constant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            Constant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->__(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            Constant::MODVAR_REGISTRATION_ENABLED                  => Constant::DEFAULT_REGISTRATION_ENABLED,
            Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->__(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
            Constant::MODVAR_REGISTRATION_VERIFICATION_MODE        => Constant::DEFAULT_REGISTRATION_VERIFICATION_MODE,
            Constant::MODVAR_REQUIRE_UNIQUE_EMAIL                  => Constant::DEFAULT_REQUIRE_UNIQUE_EMAIL,
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
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(Constant::DATETIME_FORMAT);

        // Anonymous
        $record = array(
            'uid'           => 1,
            'uname'         => 'guest',
            'email'         => '',
            'pass'          => '',
            'passreminder'  => '',
            'activated'     => Constant::ACTIVATED_ACTIVE,
            'approved_date' => '1970-01-01 00:00:00',
            'approved_by'   => 0,
            'user_regdate'  => '1970-01-01 00:00:00',
            'lastlogin'     => '1970-01-01 00:00:00',
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        $user = new \UsersModule\Entity\User;
        $user->merge($record);
        $this->entityManager->persist($user);

        // Admin
        $record = array(
            'uid'           => 2,
            'uname'         => 'admin',
            'email'         => '',
            'pass'          => '1$$dc647eb65e6711e155375218212b3964',
            'passreminder'  => '',
            'activated'     => Constant::ACTIVATED_ACTIVE,
            'approved_date' => $nowUTCStr,
            'approved_by'   => 2,
            'user_regdate'  => $nowUTCStr,
            'lastlogin'     => '1970-01-01 00:00:00',
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        $user = new \UsersModule\Entity\User;
        $user->merge($record);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }
}
