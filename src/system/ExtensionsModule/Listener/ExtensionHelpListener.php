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

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuEvent;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Api\ApiInterface\PageAssetApiInterface;
use Zikula\ThemeModule\Engine\Asset;

/**
 * Class ExtensionHelpListener
 */
class ExtensionHelpListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var PermissionApiInterface
     */
    private $permissionsApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PageAssetApiInterface
     */
    protected $assetApi;

    /**
     * @var Asset
     */
    protected $assetHelper;

    public function __construct(
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        Filesystem $fileSystem,
        TranslatorInterface $translator,
        PageAssetApiInterface $assetApi,
        Asset $assetHelper
    ) {
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
        $this->permissionsApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->fileSystem = $fileSystem;
        $this->translator = $translator;
        $this->assetApi = $assetApi;
        $this->assetHelper = $assetHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionMenuEvent::class => 'addHelpMenu'
        ];
    }

    public function addHelpMenu(ExtensionMenuEvent $event): void
    {
        // return if not collection admin links
        if (ExtensionMenuInterface::TYPE_ADMIN !== $event->getMenuType()) {
            return;
        }
        if (!$this->permissionsApi->hasPermission($event->getBundleName() . '::Help', '::', ACCESS_ADMIN)) {
            return;
        }

        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $extension = $this->kernel->getBundle($event->getBundleName());
        $helpPath = $extension->getPath() . '/Resources/docs/help/';

        // return if extension does not provide any docs
        if (!$this->fileSystem->exists($helpPath . $locale . '/README.md')) {
            if ('en' === $locale) {
                return;
            }
            // fallback to English
            $locale = 'en';
        }
        if (!$this->fileSystem->exists($helpPath . $locale . '/README.md')) {
            return;
        }

        $helpUiMode = $this->variableApi->get('ZikulaExtensionsModule', 'helpUiMode', 'modal');
        if (!in_array($helpUiMode, ['modal', 'sidebar-left', 'sidebar-right'], true)) {
            $helpUiMode = 'sidebar-right';
        }

        $event->getMenu()->addChild($this->translator->trans('Help'), [
            'route' => 'zikulaextensionsmodule_help_index',
            'routeParameters' => ['moduleName' => $event->getBundleName()]
        ])
            ->setAttribute('icon', 'fas fa-question-circle')
            ->setLinkAttribute('class', 'module-help')
            ->setLinkAttribute('data-help-mode', $helpUiMode)
        ;

        // add JS file for help UI
        $this->assetApi->add(
            'javascript',
            $this->assetHelper->resolve(
                '@ZikulaExtensionsModule:js/Zikula.Extensions.Extension.Help.' . ('modal' === $helpUiMode ? 'Modal' : 'Sidebar') . '.js'
            )
        );
        if ('sidebar-left' === $helpUiMode) {
            $this->assetApi->add(
                'stylesheet',
                $this->assetHelper->resolve(
                    '@ZikulaExtensionsModule:css/navbar-fixed-left.min.css'
                )
            );
        } elseif ('sidebar-right' === $helpUiMode) {
            $this->assetApi->add(
                'stylesheet',
                $this->assetHelper->resolve(
                    '@ZikulaExtensionsModule:css/navbar-fixed-right.min.css'
                )
            );
        }
    }
}
