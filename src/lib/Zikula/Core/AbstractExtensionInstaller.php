<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Common\Translator\Translator;
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
     * upgrade the extension
     *
     * @param string $oldVersion version being upgraded
     *
     * @return bool true if successful, false otherwise
     */
    abstract public function upgrade($oldVersion);

    /**
     * delete the extension
     *
     * @return bool true if successful, false otherwise
     */
    abstract public function uninstall();

    public function setBundle(AbstractBundle $bundle)
    {
        $this->bundle = $bundle;
        $this->name = $bundle->getName();
        if ($this->container) {
            // both here and in `setContainer` so either method can be called first.
            $this->container->get(Translator::class)->setDomain($this->bundle->getTranslationDomain());
        }
        $this->hookApi = new MockHookApi();
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTranslator($container->get(Translator::class));
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->schemaTool = $container->get(SchemaHelper::class);
        $this->extensionName = $this->name; // for ExtensionVariablesTrait
        $this->variableApi = $container->get(VariableApi::class); // for ExtensionVariablesTrait
        if ($this->bundle) {
            $this->translator->setDomain($this->bundle->getTranslationDomain());
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
        if (!$this->container->get('request_stack')->getCurrentRequest()->hasSession()) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->container->get('request_stack')->getCurrentRequest()->getSession()->getFlashBag()->add($type, $message);
    }
}
