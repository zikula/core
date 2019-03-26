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

namespace Zikula\ExtensionsModule\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\LinkContainer\LinkContainerCollector;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
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
     *
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param Asset $assetHelper
     *
     * @return Response symfony response object
     */
    public function headerAction(ExtensionRepositoryInterface $extensionRepository, Asset $assetHelper)
    {
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        $caller = $this->get('request_stack')->getMasterRequest()->attributes->all();
        $caller['info'] = $extensionRepository->get($caller['_zkModule']);
        $adminImagePath = $assetHelper->resolve('@' . $caller['_zkModule'] . ':images/admin.png');

        return $this->render("@ZikulaExtensionsModule/ExtensionsInterface/header.html.twig", [
            'caller' => $caller,
            'title' => ('' !== $currentRequest->attributes->get('title')) ? $currentRequest->attributes->get('title') : $caller['info']['displayname'],
            'titlelink' => ('' !== $currentRequest->attributes->get('titlelink')) ? $currentRequest->attributes->get('titlelink') : false,
            'setpagetitle' => (true === $currentRequest->attributes->get('setpagetitle')) ? $currentRequest->attributes->get('setpagetitle') : false,
            'insertflashes' => (true === $currentRequest->attributes->get('insertflashes')) ? $currentRequest->attributes->get('insertflashes') : false,
            'menufirst' => (true === $currentRequest->attributes->get('menufirst')) ? $currentRequest->attributes->get('menufirst') : false,
            'type' => ('admin' === $currentRequest->attributes->get('type')) ? $currentRequest->attributes->get('type') : 'user',
            'image' => (true === $currentRequest->attributes->get('image')) ? $adminImagePath : false,
        ]);
    }

    /**
     * @Route("/footer")
     *
     * Module footer
     *
     * @return Response symfony response object
     */
    public function footerAction()
    {
        return $this->render("@ZikulaExtensionsModule/ExtensionsInterface/footer.html.twig", [
            'caller' => $this->get('request_stack')->getMasterRequest()->attributes->all()
        ]);
    }

    /**
     * @Route("/help")
     *
     * display the module help page
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function helpAction()
    {
        return $this->render("@ZikulaExtensionsModule/ExtensionsInterface/help.html.twig");
    }

    /**
     * @Route("/breadcrumbs", methods = {"GET"})
     *
     * Admin breadcrumbs
     *
     * @param ExtensionRepositoryInterface $extensionRepository
     *
     * @return Response symfony response object
     */
    public function breadcrumbsAction(ExtensionRepositoryInterface $extensionRepository)
    {
        $caller = $this->get('request_stack')->getMasterRequest()->attributes->all();
        $caller['info'] = $extensionRepository->get($caller['_zkModule']);

        return $this->render("@ZikulaExtensionsModule/ExtensionsInterface/breadcrumbs.html.twig", [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/links")
     *
     * Open the admin container
     *
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param LinkContainerCollector $linkCollector
     *
     * @return Response symfony response object
     */
    public function linksAction(ExtensionRepositoryInterface $extensionRepository, LinkContainerCollector $linkCollector)
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        $caller = $this->get('request_stack')->getMasterRequest()->attributes->all();
        $caller['info'] = $extensionRepository->get($caller['_zkModule']);
        // your own links array
        $links = ('' !== $currentRequest->attributes->get('links')) ? $currentRequest->attributes->get('links') : '';
        // you can pass module name you want to get links for but
        $modname = ('' !== $currentRequest->attributes->get('modname')) ? $currentRequest->attributes->get('modname') : $caller['_zkModule'];
        // menu css
        $menu_css = [
            'menuId' => ('' !== $currentRequest->attributes->get('menuid')) ? $currentRequest->attributes->get('menuid') : '',
            'menuClass' => ('' !== $currentRequest->attributes->get('menuclass')) ? $currentRequest->attributes->get('menuclass') : 'navbar-nav',
            'menuItemClass' => ('' !== $currentRequest->attributes->get('itemclass')) ? $currentRequest->attributes->get('itemclass') : '',
            'menuFirstItemClass' => ('' !== $currentRequest->attributes->get('last')) ? $currentRequest->attributes->get('first') : '',
            'menuLastItemClass' => ('' !== $currentRequest->attributes->get('first')) ? $currentRequest->attributes->get('last') : ''
        ];

        // no own links array
        if (empty($links)) {
            // define type - default
            $links_type = 'user';
            // detect from masterRequest
            $links_type = ('' !== $masterRequest->attributes->get('type')) ? $masterRequest->attributes->get('type') : $links_type;
            // passed to currentRequest most important
            $links_type = ('' !== $currentRequest->attributes->get('type')) ? $currentRequest->attributes->get('type') : $links_type;
            //get the menu links
            $links = $linkCollector->getLinks($modname, $links_type);
        }

        $template = '' !== $currentRequest->attributes->get('template')
            ? $currentRequest->attributes->get('template')
            : '@ZikulaExtensionsModule/ExtensionsInterface/links.html.twig';

        return $this->render($template, [
            'caller' => $caller,
            'menu_css' => $menu_css,
            'links' => $links,
            'current_path' => $masterRequest->getPathInfo()
        ]);
    }
}
