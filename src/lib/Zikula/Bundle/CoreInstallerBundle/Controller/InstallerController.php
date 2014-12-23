<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class InstallerController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
class InstallerController
{
    private $router;
    private $twig;
    private $util;

    /**
     * Constructor.
     *
     * @param RouterInterface       $router          The route generator
     * @param \Twig_Environment     $twig            The twig environment
     */
    public function __construct(RouterInterface $router, \Twig_Environment $twig, $util)
    {
        $this->router = $router;
        $this->twig = $twig;
        $this->util = $util;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function installAction(Request $request)
    {
        return new Response($this->twig->render("ZikulaCoreInstallerBundle:Install:layout.html.twig", array('param' => 177)), 200, array('Content-Type' => 'text/html'));
    }
}
