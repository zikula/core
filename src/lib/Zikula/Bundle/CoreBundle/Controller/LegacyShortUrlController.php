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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class LegacyShortUrlController
 * This controller is a service defined in `CoreBundle/Resources/config/services.xml`
 * @package Zikula\Bundle\CoreBundle\Controller
 */
class LegacyShortUrlController
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * MainController constructor.
     * @param VariableApi $variableApi
     */
    public function __construct(VariableApi $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    /**
     * Controller action to handle modules utilizing legacy shorturls.
     * Route is defined as "/{path}" and therefore collects ALL undefined paths.
     * This route MUST BE LOADED LAST.
     * The route definition is set in `CoreBundle/Resources/config/legacy_routing.xml`
     * @deprecated @todo remove at Core-2.0
     * @param Request $request
     * @return mixed|Response|PlainResponse
     */
    public function shortUrlAction(Request $request)
    {
        if ($this->variableApi->get(VariableApi::CONFIG, 'shorturls')) {

            \System::resolveLegacyShortUrl($request);

            $module = $request->attributes->get('_zkModule');
            $type = $request->attributes->get('_zkType', 'user');
            $func = $request->attributes->get('_zkFunc', 'index');
            $arguments = $request->attributes->get('_zkArgs');

            // get module information
            $modinfo = \ModUtil::getInfoFromName($module);
            if (!$modinfo) {
                throw new NotFoundHttpException(__('Page not found.'));
            }

            $return = \ModUtil::func($modinfo['name'], $type, $func, $arguments);

            if (false !== $return) {
                if (false === $return instanceof Response) {
                    $response = new Response($return);
                } else {
                    $response = $return;
                }
                $response->legacy = true;

                return $response;
            }
        }
        throw new ResourceNotFoundException();
    }
}