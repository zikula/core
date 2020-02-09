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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;

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
     * @var ControllerHelper
     */
    protected $controllerHelper;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->controllerHelper = $container->get(ControllerHelper::class);
        $this->translator = $container->get('translator');
    }
}
