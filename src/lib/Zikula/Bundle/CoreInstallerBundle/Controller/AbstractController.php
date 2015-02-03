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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreInstallerBundle\Util\ControllerUtil;
use Zikula\Bundle\CoreInstallerBundle\Util\ConfigUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
abstract class AbstractController
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var EngineInterface
     */
    protected $templatingService;
    /**
     * @var ControllerUtil
     */
    protected $util;
    /**
     * @var FormFactory
     */
    protected $form;
    /**
     * @var ConfigUtil
     */
    protected $configUtil;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $this->container->get('router');
        $this->templatingService = $this->container->get('templating');
        $this->form = $this->container->get('form.factory');
        $this->util = $this->container->get('core_installer.controller.util');
        $this->configUtil = $this->container->get('core_installer.config.util');
    }
}
