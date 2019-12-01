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
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreInstallerBundle\Helper\ControllerHelper;
use Zikula\Common\Translator\Translator;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Response\PlainResponse;

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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->controllerHelper = $this->container->get(ControllerHelper::class);
        $this->translator = $container->get(Translator::class);
    }

    protected function renderResponse(string $view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new PlainResponse();
        }
        $response->setContent($this->container->get('twig')->render($view, $parameters));

        return $response;
    }
}
