<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Common\Translator\TranslatorTrait;
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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var AbstractBundle
     */
    protected $bundle;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Zikula\Core\Doctrine\Helper\SchemaHelper
     */
    protected $schemaTool;

    /**
     * @var MockHookApi
     */
    protected $hookApi;

    /**
     * initialise the extension
     *
     * @return bool true on success, false otherwise
     */
    abstract public function install();

    /**
     * upgrade the blocks extension
     *
     * @param string $oldversion version being upgraded
     *
     * @return bool true if successful, false otherwise
     */
    abstract public function upgrade($oldversion);

    /**
     * delete the blocks extension
     *
     * Since the blocks extension should never be deleted we'all always return false here
     * @return bool false
     */
    abstract public function uninstall();

    public function setBundle(AbstractBundle $bundle)
    {
        $this->bundle = $bundle;
        $this->name = $bundle->getName();
        if ($this->container) {
            // both here and in `setContainer` so either method can be called first.
            $this->container->get('translator')->setDomain($this->bundle->getTranslationDomain());
        }
        $this->hookApi = new MockHookApi();
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTranslator($container->get('translator'));
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->schemaTool = $container->get('zikula_core.common.doctrine.schema_tool');
        $this->extensionName = $this->name; // for ExtensionVariablesTrait
        $this->variableApi = $container->get('zikula_extensions_module.api.variable'); // for ExtensionVariablesTrait
        if ($this->bundle) {
            $container->get('translator')->setDomain($this->bundle->getTranslationDomain());
        }
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Convenience shortcut to add a session flash message.
     * @param string $type
     * @param string $message
     */
    public function addFlash($type, $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }
}
