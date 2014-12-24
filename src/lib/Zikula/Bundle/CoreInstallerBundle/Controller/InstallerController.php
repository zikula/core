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
use Assetic\Factory\LazyAssetManager as AssetManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class InstallerController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
class InstallerController
{
    private $router;
    private $templatingService;
    private $util;

    /**
     * Constructor.
     *
     * @param RouterInterface $router The route generator
     * @param EngineInterface $templatingService
     * @param $util
     */
    public function __construct(RouterInterface $router, EngineInterface $templatingService, $util)
    {
        $this->router = $router;
        $this->templatingService = $templatingService;
        $this->util = $util;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function installAction(Request $request)
    {
        return $this->templatingService->renderResponse("ZikulaCoreInstallerBundle:Install:layout.html.twig");
    }
}
