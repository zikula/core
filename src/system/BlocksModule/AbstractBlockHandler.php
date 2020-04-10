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

namespace Zikula\BlocksModule;

use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

abstract class AbstractBlockHandler implements BlockHandlerInterface
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var AbstractExtension
     */
    protected $extension;

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

    public function __construct(
        AbstractExtension $extension,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        Environment $twig
    ) {
        $this->extension = $extension;
        $this->extensionName = $extension->getName(); // for ExtensionVariablesTrait
        $this->requestStack = $requestStack;
        $this->setTranslator($translator); // for TranslatorTrait
        $this->variableApi = $variableApi; // for ExtensionVariablesTrait
        $this->permissionApi = $permissionApi;
        $this->twig = $twig;
        $this->boot($extension);
    }

    /**
     * Boot the handler.
     */
    protected function boot(AbstractExtension $extension): void
    {
        // load optional bootstrap
        $bootstrap = $extension->getPath() . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
    }

    public function getFormClassName(): string
    {
        return '';
    }

    public function getFormOptions(): array
    {
        return [];
    }

    public function getFormTemplate(): string
    {
        return '@ZikulaBlocksModule/Block/default_modify.html.twig';
    }

    public function getPropertyDefaults(): array
    {
        return [];
    }

    public function display(array $properties): string
    {
        return nl2br(implode("\n", $properties));
    }

    public function getType(): string
    {
        // default to the ClassName without the `Block` suffix
        // note: This string is intentionally left untranslated.
        $fqCn = get_class($this);
        $pos = mb_strrpos($fqCn, '\\');

        return mb_substr($fqCn, $pos + 1, -5);
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @throws LogicException
     */
    protected function addFlash(string $type, string $message): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }
        if (!$request->hasSession()) {
            throw new LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $request->getSession()->getFlashBag()->add($type, $message);
    }

    /**
     * Returns a rendered view.
     */
    public function renderView(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    /**
     * Convenience shortcut to check if user has requested permissions.
     */
    protected function hasPermission(
        string $component = null,
        string $instance = null,
        int $level = null,
        int $user = null
    ): bool {
        return $this->permissionApi->hasPermission($component, $instance, $level, $user);
    }

    public function getExtension(): AbstractExtension
    {
        return $this->extension;
    }
}
