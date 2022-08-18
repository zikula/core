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

namespace Zikula\ExtensionsBundle\Installer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsBundle\AbstractExtension;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsBundle\ExtensionVariablesTrait;

/**
 * Base class for extension installation and upgrade routines.
 */
abstract class AbstractExtensionInstaller implements ExtensionInstallerInterface
{
    use ExtensionVariablesTrait;
    use TranslatorTrait;

    protected string $name;

    protected ObjectManager $entityManager;

    public function __construct(
        protected readonly AbstractExtension $extension,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly SchemaHelper $schemaTool,
        protected readonly RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->name = $extension->getName();
        $this->entityManager = $managerRegistry->getManager();
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
