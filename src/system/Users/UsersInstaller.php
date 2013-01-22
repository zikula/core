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

namespace Users;

use DoctrineHelper;
use DataUtil;
use ModUtil;
use System;
use DateTime;
use DateTimeZone;
use ServiceUtil;
use Zikula_Exception_Fatal;
use DBUtil;
use EventUtil;
use HookUtil;
use Users\Constant as UsersConstant;

/**
 * Provides module installation and upgrade services for the Users module.
 */
class UsersInstaller extends \Zikula_AbstractInstaller
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
            'Users\Entity\UserEntity',
            'Users\Entity\UserAttributeEntity',
            'Users\Entity\UserSessionEntity',
            'Users\Entity\UserVerificationEntity'
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
            array('Users\Listener\PendingContentListener', 'pendingContentListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.login.veto',
            array('Users\Listener\ForcedPasswordChangeListener', 'forcedPasswordChangeListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'user.logout.succeeded',
            array('Users\Listener\ClearUsersNamespaceListener', 'clearUsersNamespaceListener'));
        EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.exception',
            array('Users\Listener\ClearUsersNamespaceListener', 'clearUsersNamespaceListener'));

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
//                this is done in upgrade script.
//                try {
//                    DoctrineHelper::createSchema($this->entityManager, array('Users\Entity\UserAttributeEntity'));
//                } catch (\Exception $e) {
//                    return false;
//                }
                $this->migrateAttributes();
            case '2.2.1':
                // This is the current version: add 2.2.1 --> next when appropriate

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
            UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS              => UsersConstant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
            UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_PAGE                => UsersConstant::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
            UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_ROW                 => UsersConstant::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
            UsersConstant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH               => UsersConstant::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
            UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->__(/* Anonymous (guest) account display name */'Guest'),
            UsersConstant::MODVAR_AVATAR_IMAGE_PATH                     => UsersConstant::DEFAULT_AVATAR_IMAGE_PATH,
            UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL              => UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD           => UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            UsersConstant::MODVAR_EXPIRE_DAYS_REGISTRATION              => UsersConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            UsersConstant::MODVAR_GRAVATARS_ENABLED                     => UsersConstant::DEFAULT_GRAVATARS_ENABLED,
            UsersConstant::MODVAR_GRAVATAR_IMAGE                        => UsersConstant::DEFAULT_GRAVATAR_IMAGE,
            UsersConstant::MODVAR_HASH_METHOD                           => UsersConstant::DEFAULT_HASH_METHOD,
            UsersConstant::MODVAR_ITEMS_PER_PAGE                        => UsersConstant::DEFAULT_ITEMS_PER_PAGE,
            UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            UsersConstant::MODVAR_LOGIN_METHOD                          => UsersConstant::DEFAULT_LOGIN_METHOD,
            UsersConstant::MODVAR_LOGIN_WCAG_COMPLIANT                  => UsersConstant::DEFAULT_LOGIN_WCAG_COMPLIANT,
            UsersConstant::MODVAR_MANAGE_EMAIL_ADDRESS                  => UsersConstant::DEFAULT_MANAGE_EMAIL_ADDRESS,
            UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH               => UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            UsersConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED       => UsersConstant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION        => '',
            UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER          => '',
            UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE        => UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
            UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN               => UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->__(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            UsersConstant::MODVAR_REGISTRATION_ENABLED                  => UsersConstant::DEFAULT_REGISTRATION_ENABLED,
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->__(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
            UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE        => UsersConstant::DEFAULT_REGISTRATION_VERIFICATION_MODE,
            UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL                  => UsersConstant::DEFAULT_REQUIRE_UNIQUE_EMAIL,
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
        $nowUTCStr = $nowUTC->format(UsersConstant::DATETIME_FORMAT);

        // Anonymous
        $record = array(
            'uid'           => 1,
            'uname'         => 'guest',
            'email'         => '',
            'pass'          => '',
            'passreminder'  => '',
            'activated'     => UsersConstant::ACTIVATED_ACTIVE,
            'approved_date' => '1970-01-01 00:00:00',
            'approved_by'   => 0,
            'user_regdate'  => '1970-01-01 00:00:00',
            'lastlogin'     => '1970-01-01 00:00:00',
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        $user = new \Users\Entity\UserEntity;
        $user->merge($record);
        $this->entityManager->persist($user);

        // Admin
        $record = array(
            'uid'           => 2,
            'uname'         => 'admin',
            'email'         => '',
            'pass'          => '1$$dc647eb65e6711e155375218212b3964',
            'passreminder'  => '',
            'activated'     => UsersConstant::ACTIVATED_ACTIVE,
            'approved_date' => $nowUTCStr,
            'approved_by'   => 2,
            'user_regdate'  => $nowUTCStr,
            'lastlogin'     => '1970-01-01 00:00:00',
            'theme'         => '',
            'ublockon'      => 0,
            'ublock'        => '',
        );
        $user = new \Users\Entity\UserEntity;
        $user->merge($record);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }


    private function migrateAttributes()
    {
        $dataset = DBUtil::selectObjectArray('users');
        $em = $this->getEntityManager();
        foreach ($dataset as $data) {
            if (!isset($data['__ATTRIBUTES__'])) {
                continue;
            }

            $user = $em->getRepository('Users\Entity\UserEntity')->findOneBy(array('uid' => $data['uid']));
            foreach ($data['__ATTRIBUTES__'] as $name => $value) {
                $user->setAttribute($name ,$value);
            }

            $em->flush();
        }
    }
}
