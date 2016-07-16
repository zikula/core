<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule;

use Zikula\Core\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the zauth module.
 */
class ZAuthModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        'Zikula\ZAuthModule\Entity\AuthenticationMappingEntity',
        'Zikula\ZAuthModule\Entity\UserVerificationEntity',
    ];

    public function install()
    {
        $this->schemaTool->create($this->entities);
        $this->setVars($this->getDefaultModvars());

        return true;
    }

    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '1.0.0':
                // current version
        }

        return true;
    }

    public function uninstall()
    {
        $this->schemaTool->drop($this->entities);

        return true;
    }

    /**
     * @return array An array of all current module variables, with their default values, suitable for {@link setVars()}
     */
    private function getDefaultModvars()
    {
        return [
            ZAuthConstant::MODVAR_HASH_METHOD => ZAuthConstant::DEFAULT_HASH_METHOD,
            ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH => ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED => ZAuthConstant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED => ZAuthConstant::DEFAULT_PASSWORD_REMINDER_ENABLED,
            ZAuthConstant::MODVAR_PASSWORD_REMINDER_MANDATORY => ZAuthConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL => ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD => ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
            ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION => ZAuthConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
            ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED => ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED,
            ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER => '',
            ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION => '',
        ];
    }
}
