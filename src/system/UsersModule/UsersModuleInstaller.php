<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule;

use Zikula\Core\AbstractExtensionInstaller;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class UsersModuleInstaller
 */
class UsersModuleInstaller extends AbstractExtensionInstaller
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
        $classes = [
            'Zikula\UsersModule\Entity\UserEntity',
            'Zikula\UsersModule\Entity\UserAttributeEntity',
            'Zikula\UsersModule\Entity\UserSessionEntity',
            'Zikula\UsersModule\Entity\UserVerificationEntity',
        ];
        try {
            $this->schemaTool->create($classes);
        } catch (\Exception $e) {
            return false;
        }

        // Set default values and modvars for module
        $this->defaultdata();
        $this->setVars($this->getDefaultModvars());
        $this->container->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'authenticationMethodsStatus', ['native_uname' => true]);

        // Register hook bundles
        $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
        $this->hookApi->installProviderHooks($this->bundle->getMetaData());

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
     * @return bool|string True on success, last valid version string or false if fails.
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '2.2.0': // version shipped with Core 1.3.5 -> current 1.3.x
                // add new table
                $this->schemaTool->create(['Zikula\UsersModule\Entity\UserAttributeEntity']);
                $this->migrateAttributes();
            case '2.2.1':
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
            case '2.2.2':
                if ($this->getVar('gravatarimage', null) == 'gravatar.gif') {
                    $this->setVar('gravatarimage', 'gravatar.jpg');
                }
            case '2.2.3':
                // Nothing to do.
            case '2.2.4':
                $connection = $this->entityManager->getConnection();
                $sql = "UPDATE users_attributes SET value='gravatar.jpg' WHERE value='gravatar.gif'";
                $stmt = $connection->prepare($sql);
                $stmt->execute();
            case '2.2.5':
                $modvarsToConvertToBool = [
                    UsersConstant::MODVAR_GRAVATARS_ENABLED,
                    UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS,
                    UsersConstant::MODVAR_REGISTRATION_ENABLED,
                    UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED,
                    UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN,
                    UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS,
                    UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS,
                    UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS
                ];
                foreach ($modvarsToConvertToBool as $modvarToConvert) {
                    $this->setVar($modvarToConvert, (bool) $this->getVar($modvarToConvert));
                }
                $this->schemaTool->update(['Zikula\UsersModule\Entity\UserEntity']);
                $this->delVar('login_redirect');
            case '2.2.8':
                $this->container->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'authenticationMethodsStatus', ['native_uname' => true]);
            case '2.2.9':
                // @todo expire all sessions so everyone has to login again (to force migration)
                // @todo migrate modvar values to ZAuth (see $this->getMigratedModVarNames())
                // @todo remove modvars no longer used
                // @todo update users table and set pass = '' where pass = 'NO_USERS_AUTHENTICATION'
                // current version
        }

        /**
         * Update successful.
         */
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
        return [
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
            UsersConstant::MODVAR_ITEMS_PER_PAGE                        => UsersConstant::DEFAULT_ITEMS_PER_PAGE,
            UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION        => '',
            UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER          => '',
            UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN               => UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->__(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            UsersConstant::MODVAR_REGISTRATION_ENABLED                  => UsersConstant::DEFAULT_REGISTRATION_ENABLED,
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->__(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
        ];
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
        $record = [
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
        ];
        $user = new \Zikula\UsersModule\Entity\UserEntity();
        $user->merge($record);
        $this->entityManager->persist($user);

        // Admin
        $record = [
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
        ];
        $user = new \Zikula\UsersModule\Entity\UserEntity();
        $user->merge($record);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }

    /**
     * migrate all data from the objectdata_attributes table to the users_attributes
     * where object_type = 'users'
     */
    private function migrateAttributes()
    {
        $connection = $this->entityManager->getConnection();
        $sqls = [];
        // copy data from objectdata_attributes to users_attributes
        $sqls[] = 'INSERT INTO users_attributes
                    (user_id, name, value)
                    SELECT object_id, attribute_name, value
                    FROM objectdata_attributes
                    WHERE object_type = \'users\'
                    ORDER BY object_id, attribute_name';
        // remove old data
        $sqls[] = 'DELETE FROM objectdata_attributes
                    WHERE object_type = \'users\'';
        foreach ($sqls as $sql) {
            $stmt = $connection->prepare($sql);
            $stmt->execute();
        }
    }

    /**
     * These modvar names used to have UsersConstant values, but have been moved to ZAuthConstant and maintain their actual values.
     * @return array
     */
    private function getMigratedModVarNames()
    {
        return [
            ZAuthConstant::MODVAR_HASH_METHOD,
            ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH,
            ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED, // convert to bool
            ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED, // convert to bool
            ZAuthConstant::MODVAR_PASSWORD_REMINDER_MANDATORY, // convert to bool
        ];
    }
}
