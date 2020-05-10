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

namespace Zikula\ExtensionsModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeModule\Engine\Asset;

/**
 * @Route("/extensionsinterface")
 */
class ExtensionsInterfaceController extends AbstractController
{
    /**
     * @Route("/header")
     *
     * Module header
     */
    public function headerAction(
        RequestStack $requestStack,
        ExtensionRepositoryInterface $extensionRepository,
        Asset $assetHelper
    ): Response {
        $currentRequest = $requestStack->getCurrentRequest();
        $caller = $requestStack->getMasterRequest()->attributes->all();
        $caller['info'] = !empty($caller['_zkModule']) ? $extensionRepository->get($caller['_zkModule']) : [];

        return $this->render('@ZikulaExtensionsModule/ExtensionsInterface/header.html.twig', [
            'caller' => $caller,
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
     * @Route("/footer")
     *
     * Module footer
     */
    public function footerAction(RequestStack $requestStack): Response
    {
        return $this->render('@ZikulaExtensionsModule/ExtensionsInterface/footer.html.twig', [
            'caller' => $requestStack->getMasterRequest()->attributes->all()
        ]);
    }

    /**
     * @Route("/breadcrumbs", methods = {"GET"})
     *
     * Admin breadcrumbs
     */
    public function breadcrumbsAction(
        RequestStack $requestStack,
        ExtensionRepositoryInterface $extensionRepository
    ): Response {
        $caller = $requestStack->getMasterRequest()->attributes->all();
        $caller['info'] = $extensionRepository->get($caller['_zkModule']);

        return $this->render('@ZikulaExtensionsModule/ExtensionsInterface/breadcrumbs.html.twig', [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/links")
     *
     * Open the admin container
     */
    public function linksAction(
        RequestStack $requestStack,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionMenuCollector $extensionMenuCollector
    ): Response {
        /** @var Request $masterRequest */
        $masterRequest = $requestStack->getMasterRequest();
        /** @var Request $currentRequest */
        $currentRequest = $requestStack->getCurrentRequest();
        $caller = $masterRequest->attributes->all();
        $caller['info'] = !empty($caller['_zkModule']) ? $extensionRepository->get($caller['_zkModule']) : [];
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
            // detect from masterRequest
            $linksType = '' !== $masterRequest->attributes->get('type') ? $masterRequest->attributes->get('type') : $linksType;
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
            : '@ZikulaExtensionsModule/ExtensionsInterface/links.html.twig';

        return $this->render($template, [
            'caller' => $caller,
            'menu_css' => $menu_css,
            'extensionMenu' => $extensionMenu,
            'current_path' => $masterRequest->getPathInfo()
        ]);
    }
}
