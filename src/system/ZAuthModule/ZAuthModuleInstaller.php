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

namespace Zikula\ZAuthModule;

use Exception;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;

/**
 * Installation and upgrade routines for the zauth module.
 */
class ZAuthModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        AuthenticationMappingEntity::class,
        UserVerificationEntity::class
    ];

    public function install(): bool
    {
        foreach ($this->entities as $entity) {
            try {
                $this->schemaTool->create([
                    $entity
                ]);
            } catch (Exception $exception) {
                if (UserVerificationEntity::class !== $entity) {
                    throw $exception;
                }
                // silently fail. This is because on core upgrade the UserVerificationEntity already exists from the UsersModule.
            }
        }
        $this->setVars($this->getDefaultModvars());

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.0.0':
                // remove password reminder
                $this->schemaTool->update([
                    AuthenticationMappingEntity::class
                ]);
                $this->delVar('password_reminder_enabled');
                $this->delVar('password_reminder_mandatory');
            case '1.0.1': // shipped with Core-2.0.15
                $this->delVar('hash_method');
                $this->setVar(ZAuthConstant::MODVAR_REQUIRE_NON_COMPROMISED_PASSWORD, ZAuthConstant::DEFAULT_REQUIRE_UNCOMPROMISED_PASSWORD);
            case '1.0.2':
                $this->setVar(ZAuthConstant::MODVAR_ITEMS_PER_PAGE, ZAuthConstant::DEFAULT_ITEMS_PER_PAGE);
                // current version
        }

        return true;
    }

    public function uninstall(): bool
    {
        $this->schemaTool->drop($this->entities);

        return true;
    }

    /**
     * @return array An array of all current module variables, with their default values, suitable for {@link setVars()}
     */
    private function getDefaultModvars(): array
    {
        return [
            ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH => ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            ZAuthConstant::MODVAR_REQUIRE_NON_COMPROMISED_PASSWORD => ZAuthConstant::DEFAULT_REQUIRE_UNCOMPROMISED_PASSWORD,
            ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED => ZAuthConstant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL => ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD => ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION => ZAuthConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED => ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED,
            ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER => '',
            ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION => '',
            ZAuthConstant::MODVAR_ITEMS_PER_PAGE => ZAuthConstant::DEFAULT_ITEMS_PER_PAGE,
        ];
    }
}
