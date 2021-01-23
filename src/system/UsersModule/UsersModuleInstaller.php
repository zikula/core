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

class UsersModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        UserEntity::class,
        UserAttributeEntity::class,
        UserSessionEntity::class
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        $this->createDefaultData();
        $this->setVars($this->getDefaultModvars());
        $this->getVariableApi()->set(VariableApi::CONFIG, 'authenticationMethodsStatus', ['native_uname' => true]);

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        // 3.0.1 shipped with Core-1.4.3
        // 3.0.5 shipped with Core-2.0.15
        // version number reset to 3.0.0 at Core 3.0.0
        $connection = $this->entityManager->getConnection();
        switch ($oldVersion) {
            case '2.9.9':
                $sql = '
                    ALTER TABLE users_attributes
                    ADD FOREIGN KEY (user_id)
                    REFERENCES users(uid)
                    ON DELETE CASCADE
                ';
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $this->delVar('password_reminder_enabled');
                $this->delVar('password_reminder_mandatory');
                $this->delVar('accountitemsperpage');
                $this->delVar('accountitemsperrow');
                $this->delVar('userimg');
                $this->setVar(UsersConstant::MODVAR_ALLOW_USER_SELF_DELETE, UsersConstant::DEFAULT_ALLOW_USER_SELF_DELETE);
                $this->schemaTool->update([UserEntity::class]);
        }

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
            UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME                => $this->trans(/* Anonymous (guest) account display name */ 'Guest'),
            UsersConstant::MODVAR_ITEMS_PER_PAGE                        => UsersConstant::DEFAULT_ITEMS_PER_PAGE,
            UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS         => UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
            UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS           => UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
            UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL => '',
            UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED        => UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
            UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN               => UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN,
            UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON          => $this->trans(/* registration disabled reason (default value, */ 'Sorry! New user registration is currently disabled.'),
            UsersConstant::MODVAR_REGISTRATION_ENABLED                  => UsersConstant::DEFAULT_REGISTRATION_ENABLED,
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS           => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS          => '',
            UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES           => $this->trans(/* illegal username list */ 'root, webmaster, admin, administrator, nobody, anonymous, username'),
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
}
