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

namespace Zikula\Core\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\AbstractBundle;
use Zikula\Core\BlockHandlerInterface;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;

abstract class AbstractBlockHandler implements BlockHandlerInterface, ContainerAwareInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * The container is intentionally hidden from the child class.
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
    public function boot(AbstractBundle $bundle)
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . "/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
        // load any plugins
        // @todo adjust this when Namespaced plugins are implemented
        \PluginUtil::loadPlugins($bundle->getPath() . "/plugins", "ModulePlugin_{$this->name}");
    }

    /**
     * Modify the block content.
     * @param Request $request
     * @param array $properties
     * @return string|array
     */
    public function modify(Request $request, array $properties)
    {
        return $request->request->get('properties', []);
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
        $this->setTranslator($container->get('translator')); // for TranslatorTrait
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
    public function renderView($view, array $parameters = array())
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
     * Creates and returns a Form instance from the type of the form.
     * @param string|FormTypeInterface $type The built type of the form
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     * @return Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance.
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     * @return FormBuilder
     */
    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('Symfony\Component\Form\Extension\Core\Type\FormType', $data, $options);
    }

    /**
     * Convenience shortcut to check if user has requested permissions.
     * @param null $component
     * @param null $instance
     * @param null $level
     * @param null $user
     * @return bool
     */
    public function hasPermission($component = null, $instance = null, $level = null, $user = null)
    {
        return $this->container->get('zikula_permissions_module.api.permission')->hasPermission($component, $instance, $level, $user);
    }
}
