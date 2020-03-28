<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the mailer module.
 */
class MailerModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        DynamicConfigDumper $configDumper,
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->kernel = $kernel;
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
                $this->setVar('smtpsecuremethod', 'ssl');
            case '1.3.2':
                // clear old modvars
                // use manual method because getVars() is not available during system upgrade
                $modVarEntities = $this->managerRegistry->getRepository(ExtensionVarEntity::class)->findBy(['modname' => $this->name]);
                $modVars = [];
                foreach ($modVarEntities as $var) {
                    $modVars[$var['name']] = $var['value'];
                }
                $this->delVars();
                $this->setVarWithDefault('charset', $modVars['charset']);
                $this->setVarWithDefault('encoding', $modVars['encoding']);
                $this->setVarWithDefault('html', $modVars['html']);
                $this->setVarWithDefault('wordwrap', $modVars['wordwrap']);
                // new modvar for 1.4.0
                $this->setVarWithDefault('enableLogging', false);

            case '1.4.0':
            case '1.4.1':
            case '1.4.2':
            case '1.4.3':
            case '1.5.0':
            case '1.5.1':
                // all swiftmailer config changes removed from previous version upgrades above
                $this->configDumper->delConfiguration('swiftmailer');
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
            'charset' => $this->kernel->getCharset(),
            'encoding' => '8bit',
            'html' => false,
            'wordwrap' => 50,
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
