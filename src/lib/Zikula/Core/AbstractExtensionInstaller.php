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

namespace Zikula\Core;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Doctrine\Helper\SchemaHelper;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;

/**
 * Installation and upgrade routines for the blocks extension
 */
abstract class AbstractExtensionInstaller implements ExtensionInstallerInterface, ContainerAwareInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var string the bundle name
     */
    protected $name;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AbstractBundle
     */
    protected $bundle;

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

    public function setBundle(AbstractBundle $bundle): void
    {
        $this->bundle = $bundle;
        $this->name = $bundle->getName();
        if ($this->container) {
            // both here and in `setContainer` so either method can be called first.
            $this->container->get('translator')->setDomain($this->bundle->getTranslationDomain());
        }
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
        if ($this->bundle) {
            $this->translator->setDomain($this->bundle->getTranslationDomain());
        }
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
