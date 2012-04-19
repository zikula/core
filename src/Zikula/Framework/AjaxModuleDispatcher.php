<?php

/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Framework\Response\Ajax\NotFoundResponse;
use Zikula\Framework\Response\Ajax\UnavailableResponse;
use Zikula\Framework\Response\Ajax\ForbiddenResponse;
use Zikula\Framework\Response\Ajax\FatalResponse;
use Zikula\Framework\Exception\NotFoundException;
use Zikula\Framework\Exception\FatalException;
use Zikula\Framework\Exception\ForbiddenException;

class AjaxModuleDispatcher
{
    public function dispatch(Request $request)
    {
        $module = $request->attributes->get('_module');
        $type = $request->attributes->get('_controller');
        $func = $request->attributes->get('_action');

        if (empty($func)) {
            $response = new NotFoundResponse(__f("Missing parameter '%s'", 'func'));
        }

        // get module information
        $modinfo = \ModUtil::getInfoFromName($module);
        if ($modinfo == false) {
            $response = new NotFoundResponse(__f("Error! The '%s' module is unknown.", \DataUtil::formatForDisplay($module)));
        }

        if (!\ModUtil::available($modinfo['name'])) {
            $response = new NotFoundResponse(__f("Error! The '%s' module is not available.", \DataUtil::formatForDisplay($module)));
        }

        if (!\ModUtil::load($modinfo['name'], $type)) {
            $response = new NotFoundResponse(__f("Error! The '%s' module is not available.", \DataUtil::formatForDisplay($module)));
        }

        // Dispatch controller.
        try {
            $response = \ModUtil::func($modinfo['name'], $type, $func);
        } catch (NotFoundException $e) {
            $response = new NotFoundResponse($e->getMessage());
        } catch (ForbiddenException $e) {
            $response = new ForbiddenResponse($e->getMessage());
        } catch (FatalException $e) {
            $response = new FatalResponse($e->getMessage());
        } catch (\PDOException $e) {
            $response = new FatalResponse($e->getMessage());
        } catch (\Exception $e) {
            $response = new FatalResponse($e->getMessage());
        }

        // Issue response.
        return $response;
    }
}