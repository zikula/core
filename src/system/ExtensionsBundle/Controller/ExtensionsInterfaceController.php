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

namespace Zikula\ExtensionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\Engine\Asset;

#[Route('/extensions/interface')]
class ExtensionsInterfaceController extends AbstractController
{
    /**
     * Extension header
     */
    #[Route('/header', name: 'zikulaextensionsbundle_extensionsinterface_header')]
    public function header(
        ZikulaHttpKernelInterface $kernel,
        RequestStack $requestStack,
        Asset $assetHelper
    ): Response {
        /** @var Request $currentRequest */
        $currentRequest = $requestStack->getCurrentRequest();

        return $this->render('@ZikulaExtensions/ExtensionsInterface/header.html.twig', [
            'caller' => $this->getCallerInfo($requestStack, $kernel),
            'title' => '' !== $currentRequest->attributes->get('title')
                ? $currentRequest->attributes->get('title')
                : ($caller['info']['displayname'] ?? ''),
            'titlelink' => '' !== $currentRequest->attributes->get('titlelink') ? $currentRequest->attributes->get('titlelink') : false,
            'setpagetitle' => true === $currentRequest->attributes->get('setpagetitle') ? $currentRequest->attributes->get('setpagetitle') : false,
            'insertflashes' => true === $currentRequest->attributes->get('insertflashes') ? $currentRequest->attributes->get('insertflashes') : false,
            'menufirst' => true === $currentRequest->attributes->get('menufirst') ? $currentRequest->attributes->get('menufirst') : false,
            'type' => 'admin' === $currentRequest->attributes->get('type') ? $currentRequest->attributes->get('type') : 'user',
        ]);
    }

    /**
     * Extension footer
     */
    #[Route('/footer', name: 'zikulaextensionsbundle_extensionsinterface_footer')]
    public function footer(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel): Response
    {
        return $this->render('@ZikulaExtensions/ExtensionsInterface/footer.html.twig', [
            'caller' => $this->getCallerInfo($requestStack, $kernel)
        ]);
    }

    /**
     * Beadcrumbs
     */
    #[Route('/breadcrumbs', name: 'zikulaextensionsbundle_extensionsinterface_breadcrumbs', methods: ['GET'])]
    public function breadcrumbs(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel): Response
    {
        return $this->render('@ZikulaExtensions/ExtensionsInterface/breadcrumbs.html.twig', [
            'caller' => $this->getCallerInfo($requestStack, $kernel)
        ]);
    }

    private function getCallerInfo(RequestStack $requestStack, ZikulaHttpKernelInterface $kernel): array
    {
        $caller = $requestStack->getMainRequest()->attributes->all();
        $caller['info'] = !empty($caller['_zkModule']) ? $kernel->getBundle($caller['_zkModule'])->getMetaData() : [];

        return $caller;
    }

    #[Route('/links', name: 'zikulaextensionsbundle_extensionsinterface_links', methods: ['GET'])]
    public function links(
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel,
        ExtensionMenuCollector $extensionMenuCollector
    ): Response {
        /** @var Request $mainRequest */
        $mainRequest = $requestStack->getMainRequest();
        /** @var Request $currentRequest */
        $currentRequest = $requestStack->getCurrentRequest();
        $caller = $this->getCallerInfo($requestStack, $kernel);
        // your own links array
        $links = '' !== $currentRequest->attributes->get('links') ? $currentRequest->attributes->get('links') : '';
        // you can pass module name you want to get links for
        $moduleName = '' !== $currentRequest->attributes->get('modname')
            ? $currentRequest->attributes->get('modname')
            : ($caller['_zkModule'] ?? '');

        // no own links array
        if (empty($links)) {
            // define type - default
            $linksType = 'user';
            // detect from mainRequest
            $linksType = '' !== $mainRequest->attributes->get('type') ? $mainRequest->attributes->get('type') : $linksType;
            // passed to currentRequest most important
            $linksType = '' !== $currentRequest->attributes->get('type') ? $currentRequest->attributes->get('type') : $linksType;
            // get the menu links
            $extensionMenu = $extensionMenuCollector->get($moduleName, $linksType);
            if (isset($extensionMenu)) {
                $extensionMenu->setChildrenAttribute('class', 'nav nav-modulelinks');
            }
        }

        // menu css
        $menu_css = [
            'menuId' => $currentRequest->attributes->get('menuid', ''),
            'menuClass' => $currentRequest->attributes->get('menuclass', ''),
            'menuItemClass' => $currentRequest->attributes->get('itemclass', ''),
            'menuFirstItemClass' => $currentRequest->attributes->get('first', ''),
            'menuLastItemClass' => $currentRequest->attributes->get('last', '')
        ];

        $template = '' !== $currentRequest->attributes->get('template')
            ? $currentRequest->attributes->get('template')
            : '@ZikulaExtensions/ExtensionsInterface/links.html.twig';

        return $this->render($template, [
            'caller' => $caller,
            'menu_css' => $menu_css,
            'extensionMenu' => $extensionMenu,
            'current_path' => $mainRequest->getPathInfo()
        ]);
    }
}
