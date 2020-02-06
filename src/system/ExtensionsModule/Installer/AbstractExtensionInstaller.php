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

namespace Zikula\ExtensionsModule\Installer;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;

/**
 * Base class for extension installation and upgrade routines.
 */
abstract class AbstractExtensionInstaller implements ExtensionInstallerInterface, ContainerAwareInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var string the extension name
     */
    protected $name;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AbstractExtension
     */
    protected $extension;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SchemaHelper
     */
    protected $schemaTool;

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

    public function setExtension(AbstractExtension $extension): void
    {
        $this->extension = $extension;
        $this->name = $extension->getName();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->extensionName = $this->name; // for ExtensionVariablesTrait
        $this->container = $container;
        if (null === $container) {
            return;
        }
        $this->setTranslator($container->get('translator'));
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->schemaTool = $container->get(SchemaHelper::class);
        $this->variableApi = $container->get(VariableApi::class); // for ExtensionVariablesTrait
    }

    /**
     * Convenience shortcut to add a session flash message.
     */
    public function addFlash(string $type, string $message): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
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
