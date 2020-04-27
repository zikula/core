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

namespace Zikula\MailerModule;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the mailer module.
 */
class MailerModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    public function __construct(
        DynamicConfigDumper $configDumper,
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->configDumper = $configDumper;
        parent::__construct($extension, $managerRegistry, $schemaTool, $requestStack, $translator, $variableApi);
    }

    public function install(): bool
    {
        $this->setVars($this->getDefaults());

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.3.1':
            case '1.3.2':
                // new modvar for 1.4.0
                $this->setVarWithDefault('enableLogging', false);
            case '1.4.0':
            case '1.4.1':
            case '1.4.2':
            case '1.4.3':
            case '1.5.0':
            case '1.5.1': // shipped with Core-2.0.15
                // all swiftmailer config changes and module-vars removed from previous version upgrades above
                $this->configDumper->delConfiguration('swiftmailer');
                $enableLogging = $this->getVar('enableLogging');
                $this->delVars();
                $this->setVar('enableLogging', $enableLogging);
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    public function uninstall(): bool
    {
        // Delete any module variables
        $this->delVars();

        // Deletion successful
        return true;
    }

    /**
     * Default module vars.
     */
    private function getDefaults(): array
    {
        return [
            'enableLogging' => false
        ];
    }

    /**
     * Set the module var but if it is not set, use the default instead.
     *
     * @param mixed $value
     */
    private function setVarWithDefault(string $key, $value = null): void
    {
        if (isset($value)) {
            $this->setVar($key, $value);
        }
        $defaults = $this->getDefaults();
        $this->setVar($key, $defaults[$key]);
    }
}
