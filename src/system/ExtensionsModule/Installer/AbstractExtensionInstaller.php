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

namespace Zikula\ExtensionsModule\Installer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;

/**
 * Base class for extension installation and upgrade routines.
 */
abstract class AbstractExtensionInstaller implements ExtensionInstallerInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var string the extension name
     */
    protected $name;

    /**
     * @var AbstractExtension
     */
    protected $extension;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var SchemaHelper
     */
    protected $schemaTool;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->extension = $extension;
        $this->name = $extension->getName();
        $this->managerRegistry = $managerRegistry;
        $this->entityManager = $managerRegistry->getManager();
        $this->schemaTool = $schemaTool;
        $this->requestStack = $requestStack;
        $this->setTranslator($translator);
        $this->extensionName = $this->name; // ExtensionVariablesTrait
        $this->variableApi = $variableApi; // ExtensionVariablesTrait
    }

    /**
     * Initialise the extension
     */
    abstract public function install(): bool;

    /**
     * Upgrade the extension.
     */
    abstract public function upgrade(string $oldVersion): bool;

    /**
     * Delete the extension.
     */
    abstract public function uninstall(): bool;

    /**
     * Convenience shortcut to add a session flash message.
     */
    public function addFlash(string $type, string $message): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            echo ucfirst($type) . ': ' . $message . "\n";

            return;
        }
        if (!$request->hasSession()) {
            throw new LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $request->getSession()->getFlashBag()->add($type, $message);
    }
}
