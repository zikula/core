<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Helper to add a Symfony Resource to the page.
 *
 * Available parameters:
 *   - resource: The Resource to add.
 *   - res:      Shorthand for "resource".
 *   - type:     The type of the resource. Can be "javascript" or "stylesheet". If no type is given, it is
 *               distinguished from the resource name.
 *
 * Example call:
 * {resource res='@ZikulaAdminModule/Resources/public/js/admin_admin_admintabs.js'}
 */
function smarty_function_resource($params, Zikula_View $view)
{
    $resource = isset($params['resource']) ? $params['resource'] : $params['res'];

    if (isset($params['type'])) {
        $type = $params['type'];
    } else {
        switch (pathinfo($resource, PATHINFO_EXTENSION)) {
            case 'css':
                $type = 'stylesheet';
                break;
            case 'js':
                $type = 'javascript';
                break;
            default:
                $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('resource', 'type')));

                return false;
        }
    }

    $kernel = $view->getContainer()->get('kernel');

    $root = realpath($kernel->getRootDir() . "/../");
    $fullPath = $kernel->locateResource($resource);
    $path = $view->getBaseUri() . substr($fullPath, strlen($root));

    PageUtil::addVar($type, $path);

    return '';
}
