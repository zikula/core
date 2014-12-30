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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Zikula\Bundle\CoreInstallerBundle\Util\ControllerUtil;

/**
 * Class DocController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
class DocController
{
    private $router;
    private $templatingService;
    private $util;
    private $lang;

    /**
     * Constructor.
     *
     * @param RouterInterface $router The route generator
     * @param EngineInterface $templatingService
     * @param $util
     */
    public function __construct(RouterInterface $router, EngineInterface $templatingService, ControllerUtil $util)
    {
        $this->router = $router;
        $this->templatingService = $templatingService;
        $this->util = $util;
        $this->lang = \ZLanguage::getLanguageCode();
    }

    /**
     * @param Request $request
     * @param string $name
     * @return Response
     */
    public function displayAction(Request $request, $name = "INSTALL")
    {
        // display e.g. docs/$this->lang/{$name}.md
        return new Response();
    }
}