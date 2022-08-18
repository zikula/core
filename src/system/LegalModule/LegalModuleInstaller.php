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

namespace Zikula\LegalModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\LegalModule\Constant as LegalConstant;

/**
 * Installs, upgrades, and uninstalls the Legal module.
 */
class LegalModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        // Set default values for the module variables
        $this->setVars([
            LegalConstant::MODVAR_LEGALNOTICE_ACTIVE             => true,
            LegalConstant::MODVAR_TERMS_ACTIVE                   => true,
            LegalConstant::MODVAR_PRIVACY_ACTIVE                 => true,
            LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE           => true,
            LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE => false,
            LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE         => false,
            LegalConstant::MODVAR_LEGALNOTICE_URL                => '',
            LegalConstant::MODVAR_TERMS_URL                      => '',
            LegalConstant::MODVAR_PRIVACY_URL                    => '',
            LegalConstant::MODVAR_ACCESSIBILITY_URL              => '',
            LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL    => '',
            LegalConstant::MODVAR_TRADECONDITIONS_URL            => '',
            LegalConstant::MODVAR_MINIMUM_AGE                    => 13,
            LegalConstant::MODVAR_EUCOOKIE                       => 0
        ]);

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.1':
                // Upgrade 1.1 -> 1.2
                $this->setVars([
                    'termsofuse'             => true,
                    'privacypolicy'          => true,
                    'accessibilitystatement' => true,
                ]);
                // no break
            case '1.2':
            // Upgrade 1.2 -> 1.3
            // Nothing to do.
            case '1.3':
                // Upgrade 1.3 -> 2.0.0
                // Convert the module variables to the new names
                $this->setVar(LegalConstant::MODVAR_TERMS_ACTIVE, $this->getVar('termsofuse', true));
                $this->delVar('termsofuse');
                $this->setVar(LegalConstant::MODVAR_PRIVACY_ACTIVE, $this->getVar('privacypolicy', true));
                $this->delVar('privacypolicy');
                $this->setVar(LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, $this->getVar('accessibilitystatement', true));
                $this->delVar('accessibilitystatement');
                // Set the new module variable -- but if Users set it for us during its upgrade, then don't overwrite it
                $this->setVar(LegalConstant::MODVAR_MINIMUM_AGE, $this->getVar(LegalConstant::MODVAR_MINIMUM_AGE, 0));
                // Set up the new persistent event handler, and any other event-related features.
                // no break
            case '2.0.0':
                // Upgrade 2.0.0 -> 2.0.1
                // add vars for new document types and optional custom urls
                $this->setVars([
                    LegalConstant::MODVAR_LEGALNOTICE_ACTIVE             => false,
                    LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE => false,
                    LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE         => false,
                    LegalConstant::MODVAR_LEGALNOTICE_URL                => '',
                    LegalConstant::MODVAR_TERMS_URL                      => '',
                    LegalConstant::MODVAR_PRIVACY_URL                    => '',
                    LegalConstant::MODVAR_ACCESSIBILITY_URL              => '',
                    LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL    => '',
                    LegalConstant::MODVAR_TRADECONDITIONS_URL            => '',
                ]);
                // no break
            case '2.0.1':
                // Nothing to do.
            case '2.0.2':
                // Upgrade 2.0.2 -> 2.1.0
                // attributes migrated by Users mod
                // @todo write upgrade for permissions?
                $this->setVar(LegalConstant::MODVAR_EUCOOKIE, 0);
                // no break
            case '2.1.0': // current version
                // nothing
            case '2.1.1':
                // nothing
            case '2.1.2':
                // nothing
            case '3.0.0':
                // nothing
            case '3.0.1':
                // nothing
            case '3.1.0':
                // nothing
            case '3.1.1':
                // nothing
            case '3.1.2':
                // future upgrades

                // The following break should be the only one in the switch, and should appear immediately prior to the default case.
                break;
            default:
        }

        // Update successful
        return true;
    }

    public function uninstall(): bool
    {
        $this->delVars();

        // Deletion successful
        return true;
    }
}
