<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\AbstractBundle;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

abstract class AbstractBlockHandler implements BlockHandlerInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var AbstractBundle
     */
    protected $bundle;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var PermissionApiInterface
     */
    protected $permissionApi;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * AbstractBlockHandler constructor.
     *
     * @param AbstractBundle $bundle
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @param VariableApiInterface $variableApi
     * @param PermissionApiInterface $permissionApi
     * @param Environment $twig
     */
    public function __construct(
        AbstractBundle $bundle,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        Environment $twig
    ) {
        $this->bundle = $bundle;
        $this->extensionName = $bundle->getName(); // for ExtensionVariablesTrait
        $this->requestStack = $requestStack;
        $this->setTranslator($translator); // for TranslatorTrait
        $this->variableApi = $variableApi; // for ExtensionVariablesTrait
        $this->permissionApi = $permissionApi;
        $this->twig = $twig;
        $this->boot($bundle);
    }

    /**
     * boot the handler
     * @param AbstractBundle $bundle
     */
    protected function boot(AbstractBundle $bundle)
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
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
        if (!$this->requestStack->getCurrentRequest()->hasSession()) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add($type, $message);
    }

    /**
     * Returns a rendered view.
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = [])
    {
        $parameters['domain'] = $this->translator->getDomain();

        return $this->twig->render($view, $parameters);
    }

    /**
     * Convenience shortcut to check if user has requested permissions.
     *
     * @param string $component
     * @param string $instance
     * @param integer $level
     * @param integer $user
     * @return bool
     */
    protected function hasPermission($component = null, $instance = null, $level = null, $user = null)
    {
        return $this->permissionApi->hasPermission($component, $instance, $level, $user);
    }

    /**
     * @return AbstractBundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
