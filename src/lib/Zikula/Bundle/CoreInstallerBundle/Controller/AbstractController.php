<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $this->util = $this->container->get('zikula_core_installer.controller.util');
        $this->configUtil = $this->container->get('zikula_core_installer.config.util');
    }
}
