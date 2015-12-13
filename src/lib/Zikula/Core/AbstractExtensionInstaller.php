<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core;

use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;

/**
 * Installation and upgrade routines for the blocks extension
 */
abstract class AbstractExtensionInstaller implements ExtensionInstallerInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var string the bundle name.
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
        $this->container = $bundle->getContainer();
        $this->container->get('translator')->setDomain($this->bundle->getTranslationDomain());
        $this->setTranslator($this->container->get('translator'));
        $this->entityManager = $this->container->get('doctrine.entitymanager');
        $this->schemaTool = $this->container->get('zikula.doctrine.schema_tool');
        $this->extensionName = $this->name; // for ExtensionVariablesTrait
        $this->variableApi = $bundle->getContainer()->get('zikula_extensions_module.api.variable'); // for ExtensionVariablesTrait
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Convenience shortcut to add a session flash message.
     * @param $type
     * @param $message
     */
    public function addFlash($type, $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }
}