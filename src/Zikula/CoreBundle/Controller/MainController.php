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

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

/**
 * Main controller.
 */
class MainController
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        RouterInterface $router,
        VariableApiInterface $variableApi
    ) {
        $this->kernel = $kernel;
        $this->router = $router;
        $this->variableApi = $variableApi;
    }

    /**
     * This controller action is designed for the "/" route (home).
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     */
    public function homeAction(Request $request): Response
    {
        $startPageInfo = $this->variableApi->getSystemVar('startController');
        if (!$startPageInfo || !$startPageInfo['controller']) {
            return new Response(''); // home page is static
        }

        $isValidStartController = true;
        $startController = $startPageInfo['controller'];
        if (!isset($startPageInfo['controller']) || !is_string($startPageInfo['controller'])) {
            $isValidStartController = false;
        } elseif (false === mb_strpos($startController, '\\') || false === mb_strpos($startController, '::')) {
            $isValidStartController = false;
        } else {
            [$vendor, $bundleName] = explode('\\', $startController);
            $bundleName = $vendor . $bundleName;
            [$fqcn, $method] = explode('::', $startController);
            if (!$this->kernel->isBundle($bundleName) || !class_exists($fqcn) || !is_callable([$fqcn, $method])) {
                $isValidStartController = false;
            }
        }

        if (!$isValidStartController) {
            return new Response(''); // home page is static
        }

        $queryParams = $requestParams = $attributes = [];
        if (null !== $startPageInfo['query']) {
            parse_str($startPageInfo['query'], $queryParams);
        }
        if (null !== $startPageInfo['request']) {
            parse_str($startPageInfo['request'], $requestParams);
        }
        if (null !== $startPageInfo['attributes']) {
            parse_str($startPageInfo['attributes'], $attributes);
        }
        $attributes['_controller'] = $startController;

        foreach ($this->router->getRouteCollection()->all() as $route => $params) {
            $defaults = $params->getDefaults();
            if (isset($defaults['_controller']) && $defaults['_controller'] === $startPageInfo['controller']) {
                $attributes['_route'] = $route;
                break;
            }
        }

        $subRequest = $request->duplicate($queryParams, $requestParams, $attributes);

        $subRequest->attributes->set('_zkBundle', $bundleName);
        $subRequest->attributes->set('_zkModule', $bundleName);
        // fix for #3929, #3932
        $request->attributes->set('_zkBundle', $bundleName);
        $request->attributes->set('_zkModule', $bundleName);

        return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
