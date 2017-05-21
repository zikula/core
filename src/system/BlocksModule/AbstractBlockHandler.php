<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\AbstractBundle;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;

abstract class AbstractBlockHandler implements BlockHandlerInterface, ContainerAwareInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * The container is intentionally hidden from the child class.
     * Use the get() method to access services from a child class.
     * @var ContainerInterface
     */
    private $container;

    /**
     * AbstractBlockHandler constructor.
     * @param AbstractBundle $bundle
     */
    public function __construct(AbstractBundle $bundle)
    {
        $this->extensionName = $bundle->getName(); // for ExtensionVariablesTrait
        $this->boot($bundle);
    }

    /**
     * boot the handler
     * @param AbstractBundle $bundle
     */
    protected function boot(AbstractBundle $bundle)
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . "/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
        // load any plugins
        // @todo adjust this when Namespaced plugins are implemented
        \PluginUtil::loadPlugins($bundle->getPath() . "/plugins", "ModulePlugin_{$bundle->getName()}");
    }

    public function getFormClassName()
    {
        return null;
    }

    public function getFormOptions()
    {
        return [];
    }

    public function getFormTemplate()
    {
        return '@ZikulaBlocksModule/Block/default_modify.html.twig';
    }

    /**
     * Display the block content.
     * @param array $properties
     * @return string
     */
    public function display(array $properties)
    {
        $content = nl2br(implode("\n", $properties));

        return $content;
    }

    /**
     * Get the type of the block (e.g. the 'name').
     * @return string
     */
    public function getType()
    {
        // default to the ClassName without the `Block` suffix
        // note: This string is intentionally left untranslated.
        $fqCn = get_class($this);
        $pos = strrpos($fqCn, '\\');

        return substr($fqCn, $pos + 1, -5);
    }

    /**
     * Implement ContainerAwareInterface - setContainer.
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTranslator($container->get('translator.default')); // for TranslatorTrait
        $this->variableApi = $container->get('zikula_extensions_module.api.variable'); // for ExtensionVariablesTrait
    }

    /**
     * @param $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Adds a flash message to the current session for type.
     * @param string $type The type
     * @param string $message The message
     * @throws \LogicException
     */
    protected function addFlash($type, $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * Returns a rendered view.
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = [])
    {
        if ($this->container->has('templating')) {
            return $this->container->get('templating')->render($view, $parameters);
        }

        if (!$this->container->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available.');
        }
        $parameters['domain'] = $this->translator->getDomain();

        return $this->container->get('twig')->render($view, $parameters);
    }

    /**
     * Convenience shortcut to check if user has requested permissions.
     * @param null $component
     * @param null $instance
     * @param null $level
     * @param null $user
     * @return bool
     */
    protected function hasPermission($component = null, $instance = null, $level = null, $user = null)
    {
        return $this->container->get('zikula_permissions_module.api.permission')->hasPermission($component, $instance, $level, $user);
    }

    /**
     * Shortcut method to fetch services from the container.
     * @param $serviceName
     * @return object
     */
    protected function get($serviceName)
    {
        return $this->container->get($serviceName);
    }

    /**
     * Shortcut method to fetch parameters from the container.
     * @param $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }
}
