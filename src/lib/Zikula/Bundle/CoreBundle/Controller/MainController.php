<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class MainController
 * This controller is a service defined in `CoreBundle/Resources/config/services.xml`
 * @package Zikula\Bundle\CoreBundle\Controller
 */
class MainController
{
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @deprecated
     * @var LegacyController
     */
    private $legacyController;

    /**
     * MainController constructor.
     * @param KernelInterface $kernelInterface
     * @param VariableApi $variableApi
     */
    public function __construct(KernelInterface $kernelInterface, VariableApi $variableApi, LegacyController $legacyController)
    {
        $this->kernel = $kernelInterface;
        $this->variableApi = $variableApi;
        $this->legacyController = $legacyController;
    }

    /**
     * This controller action is designed for the "/" route (home).
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     * and includes the condition `request.query.get('module') == ''`
     *   which means it will not be used if the query param `module` is set (which is true in legacy urls).
     *
     * @param Request $request
     * @return bool|mixed|Response|PlainResponse
     */
    public function homeAction(Request $request)
    {
        $controller = $this->variableApi->get(VariableApi::CONFIG, 'startController');
        if (!$controller) {
            // @todo remove legacyResponse at Core-2.0
            if (false !== $legacyResponse = $this->legacyController->getLegacyStartPageResponse()) {
                return $legacyResponse;
            }

            return new Response(''); // home page is static
        }
        $args = $this->variableApi->get(VariableApi::CONFIG, 'startargs');
        parse_str($args, $attributes);
        $attributes['_controller'] = $controller;
        $subRequest = $request->duplicate(null, null, $attributes);

        return $this->kernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}