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

namespace Zikula\UsersModule;

use DateTime;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserAttributeEntity;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Entity\UserSessionEntity;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Installation and upgrade routines for the users module.
 */
class UsersModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        UserEntity::class,
        UserAttributeEntity::class,
        UserSessionEntity::class
    ];

    public function install(): bool
    {
        // create the tables
        $this->schemaTool->create($this->entities);

        // Set default values and modvars for module
        $this->createDefaultData();
        $this->setVars($this->getDefaultModvars());
        $this->getVariableApi()->set(VariableApi::CONFIG, 'authenticationMethodsStatus', ['native_uname' => true]);

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        $connection = $this->entityManager->getConnection();
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '2.2.0': // version shipped with Core 1.3.5 -> current 1.3.x
                $sql = 'ALTER TABLE users ENGINE = InnoDB';
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                // add new table
                $this->schemaTool->create([
                    UserAttributeEntity::class
                ]);
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
                if ('gravatar.gif' === $this->getVar('gravatarimage', null)) {
                    $this->setVar('gravatarimage', 'gravatar.jpg');
                }
            case '2.2.3':
                // Nothing to do.
            case '2.2.4':
                $sql = "
                    UPDATE users_attributes
                    SET value = 'gravatar.jpg'
                    WHERE value = 'gravatar.gif'
                ";
                $stmt = $connection->prepare($sql);
                $stmt->execute();
            case '2.2.5':
                $modvarsToConvertToBool = [
                    UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS,
                    UsersConstant::MODVAR_REGISTRATION_ENABLED,
                    UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED,
                    UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN,
                    UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS,
                    UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS,
                    UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS
                ];
                foreach ($modvarsToConvertToBool as $modvarToConvert) {
                    $this->setVar($modvarToConvert, (bool)$this->getVar($modvarToConvert));
                }
                $this->schemaTool->update([
                    UserEntity::class
                ]);
                $this->delVar('login_redirect');
            case '2.2.8':
                $this->getVariableApi()->set(VariableApi::CONFIG, 'authenticationMethodsStatus', ['native_uname' => true]);
            case '2.2.9':
                // migrate modvar values to ZAuth and remove from Users
                $this->migrateModVarsToZAuth();
                // update users table
                $sql = "
                    UPDATE users
                    SET pass = ''
                    WHERE pass = 'NO_USERS_AUTHENTICATION'
                ";
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                // expire all sessions so everyone has to login again (to force migration)
                $this->entityManager->createQuery('DELETE FROM Zikula\UsersModule\Entity\UserSessionEntity')->execute();
            case '3.0.0':
                $this->schemaTool->update([
                    UserSessionEntity::class
                ]);
            case '3.0.1':
                $sql = '
                    ALTER TABLE users_attributes
                    ADD FOREIGN KEY (user_id)
                    REFERENCES users(uid)
                    ON DELETE CASCADE
                ';
                $stmt = $connection->prepare($sql);
                $stmt->execute();
            case '3.0.2':
                // remove password reminder
                $this->schemaTool->update([
                    UserEntity::class
                ]);
                $this->delVar('password_reminder_enabled');
                $this->delVar('password_reminder_mandatory');
            case '3.0.3':
            case '3.0.4':
                $this->schemaTool->update([UserEntity::class]);
            case '3.0.5': // shipped with Core-2.0.15
                $this->delVar('accountitemsperpage');
                $this->delVar('accountitemsperrow');
                $this->delVar('userimg');
                $this->setVar(UsersConstant::MODVAR_ALLOW_USER_SELF_DELETE, UsersConstant::DEFAULT_ALLOW_USER_SELF_DELETE);
            case '3.0.6':
                // current version
        }

        // Update successful
        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Build and return an array of all current module variables, with their default values.
     */
    private function getDefaultModvars(): array
    {
        return [
            UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS              => UsersConstant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
            UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->trans(/* Anonymous (guest) account display name */'Guest'),
            UsersConstant::MODVAR_ITEMS_PER_PAGE                        => UsersConstant::DEFAULT_ITEMS_PER_PAGE,
            UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN               => UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->trans(/* registration disabled reason (default value, */'Sorry! New user registration is currently disabled.'),
            UsersConstant::MODVAR_REGISTRATION_ENABLED                  => UsersConstant::DEFAULT_REGISTRATION_ENABLED,
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->trans(/* illegal username list */'root, webmaster, admin, administrator, nobody, anonymous, username'),
            UsersConstant::MODVAR_ALLOW_USER_SELF_DELETE                => UsersConstant::DEFAULT_ALLOW_USER_SELF_DELETE,
        ];
    }

    /**
     * Create the default data for the users module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     */
    private function createDefaultData(): void
    {
        $now = new DateTime();
        $then = new DateTime('1970-01-01 00:00:00');

        // Anonymous
        $record = [
            'uid'           => UsersConstant::USER_ID_ANONYMOUS,
            'uname'         => 'guest',
            'email'         => '',
            'activated'     => UsersConstant::ACTIVATED_ACTIVE,
            'approvedDate'  => $then,
            'approvedBy'    => UsersConstant::USER_ID_ADMIN,
            'registrationDate' => $then,
            'lastLogin'     => $then,
        ];
        $user = new UserEntity();
        $user->merge($record);
        $this->entityManager->persist($user);

        // Admin
        $record = [
            'uid'           => UsersConstant::USER_ID_ADMIN,
            'uname'         => 'admin',
            'email'         => '',
            'activated'     => UsersConstant::ACTIVATED_ACTIVE,
            'approvedDate'  => $now,
            'approvedBy'    => UsersConstant::USER_ID_ADMIN,
            'registrationDate' => $now,
            'lastLogin'     => $then,
        ];
        $user = new UserEntity();
        $user->merge($record);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }

    /**
     * Migrate all data from the objectdata_attributes table to the users_attributes
     * where object_type = 'users'
     */
    private function migrateAttributes(): void
    {
        $connection = $this->entityManager->getConnection();
        $sqls = [];
        // copy data from objectdata_attributes to users_attributes
        $sqls[] = '
            INSERT INTO users_attributes
            (user_id, name, value)
            SELECT object_id, attribute_name, value
            FROM objectdata_attributes
            WHERE object_type = \'users\'
            ORDER BY object_id, attribute_name
        ';
        // remove old data
        $sqls[] = '
            DELETE FROM objectdata_attributes
            WHERE object_type = \'users\'
        ';
        foreach ($sqls as $sql) {
            $stmt = $connection->prepare($sql);
            $stmt->execute();
        }
    }

    /**
     * v2.2.9 -> 3.0.0
     * move select modvar values to ZAuthModule.
     * change to boolean where required.
     */
    private function migrateModVarsToZAuth(): void
    {
        $migratedModVarNames = $this->getMigratedModVarNames();
        foreach ($migratedModVarNames as $migratedModVarName) {
            $value = $this->getVar($migratedModVarName);
            $this->delVar($migratedModVarName); // removes from UsersModule
            $migratedModVarName = 'reg_verifyemail' === $migratedModVarName ? ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED : $migratedModVarName;
            $value = in_array($migratedModVarName, [
                ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED,
                ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED
            ], true) ? (bool)$value : $value;
            $this->getVariableApi()->set('ZikulaZAuthModule', $migratedModVarName, $value);
        }
    }

    /**
     * These modvar names used to have UsersConstant values, but have been moved to ZAuthConstant and maintain their actual values.
     *
     * @return string[]
     */
    private function getMigratedModVarNames(): array
    {
        return [
            ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH,
            ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED, // convert to bool
            ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION,
            ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD,
            'reg_verifyemail', // convert to bool
        ];
    }
}
