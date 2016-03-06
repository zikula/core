<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Zikula\Core\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Console\Application;

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
     * @return Response symfony response object
     */
    public function headerAction()
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['info'] = \ModUtil::getInfoFromName($caller['_zkModule']);

        return $this->render("ZikulaExtensionsModule:ExtensionsInterface:header.html.twig", [
            'caller' => $caller,
            'title' => ('' != $currentRequest->attributes->get('title')) ? $currentRequest->attributes->get('title') : $caller['info']['displayname'],
            'titlelink' => ('' != $currentRequest->attributes->get('titlelink')) ? $currentRequest->attributes->get('titlelink') : false,
            'setpagetitle' => (true == $currentRequest->attributes->get('setpagetitle')) ? $currentRequest->attributes->get('setpagetitle') : false,
            'insertflashes' => (true == $currentRequest->attributes->get('insertflashes')) ? $currentRequest->attributes->get('insertflashes') : false,
            'menufirst' => (true == $currentRequest->attributes->get('menufirst')) ? $currentRequest->attributes->get('menufirst') : false,
            'type' => ('admin' == $currentRequest->attributes->get('type')) ? $currentRequest->attributes->get('type') : 'user',
            'image' => (true == $currentRequest->attributes->get('image')) ? \ModUtil::getModuleImagePath($caller['_zkModule']) : false,
        ]);
    }

    /**
     * @Route("/footer")
     *
     *
     * Module footer
     *
     * @return Response symfony response object
     */
    public function footerAction()
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');

        return $this->render("ZikulaExtensionsModule:ExtensionsInterface:footer.html.twig", [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/help")
     *
     *
     * display the module help page
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function helpAction()
    {
        return $this->render("ZikulaExtensionsModule:ExtensionsInterface:help.html.twig");
    }

    /**
     * @Route("/breadcrumbs")
     * @Method("GET")
     *
     *
     * Admin breadcrumbs
     *
     * @return Response symfony response object
     */
    public function breadcrumbsAction()
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['info'] = \ModUtil::getInfoFromName($caller['_zkModule']);

        return $this->render("ZikulaExtensionsModule:ExtensionsInterface:breadcrumbs.html.twig", [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/links")
     *
     *
     * Open the admin container
     *
     * @return Response symfony response object
     */
    public function linksAction()
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['info'] = \ModUtil::getInfoFromName($caller['_zkModule']);
        // your own links array
        $links = ('' !== $currentRequest->attributes->get('links')) ? $currentRequest->attributes->get('links') : '';
        // you can pass module name you want to get links for but
        $modname = ('' !== $currentRequest->attributes->get('modname')) ? $currentRequest->attributes->get('modname') : $caller['_zkModule'];
        // menu css
        $menu_css = [];
        $menu_css['menuId'] = ('' !== $currentRequest->attributes->get('menuid')) ? $currentRequest->attributes->get('menuid') : '';
        $menu_css['menuClass'] = ('' !== $currentRequest->attributes->get('menuclass')) ? $currentRequest->attributes->get('menuclass') : 'navbar navbar-default navbar-modulelinks navbar-modulelinks-main';
        $menu_css['menuItemClass'] = ('' !== $currentRequest->attributes->get('itemclass')) ? $currentRequest->attributes->get('itemclass') : '';
        $menu_css['menuFirstItemClass'] = ('' !== $currentRequest->attributes->get('last')) ? $currentRequest->attributes->get('first') : '';
        $menu_css['menuLastItemClass'] = ('' !== $currentRequest->attributes->get('first')) ? $currentRequest->attributes->get('last') : '';

        // no own links array
        if (empty($links)) {
            // define type - default
            $links_type = 'user';
            // detect from masterRequest
            $links_type = ('' !== $masterRequest->attributes->get('type')) ? $masterRequest->attributes->get('type') : $links_type;
            // passed to currentRequest most important
            $links_type = ('' !== $currentRequest->attributes->get('type')) ? $currentRequest->attributes->get('type') : $links_type;
            //get the menu links
            $links = $this->get('zikula.link_container_collector')->getLinks($modname, $links_type);
        }

        return $this->render("ZikulaExtensionsModule:ExtensionsInterface:links.html.twig", [
            'caller' => $caller,
            'menu_css' => $menu_css,
            'links' => $links,
            'current_path' => $masterRequest->getPathInfo()]);
    }
}
