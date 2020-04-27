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

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.5.0': // shipped with Core-1.4.3
            case '1.5.1': // shipped with Core-2.0.15
                // all swiftmailer config changes and module-vars removed from previous version upgrades above
                $this->configDumper->delConfiguration('swiftmailer');
                $enableLogging = $this->getVar('enableLogging');
                $this->delVars();
                $this->setVar('enableLogging', $enableLogging);
                // future upgrade routines
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
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
}
