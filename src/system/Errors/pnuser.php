<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Errors
 */

/**
 * Display an error
 * This function displays a generic error form
 * The template used is based on the error type passed
 * @author Brian Lindner
 * @author Brook Humphrey
 * @param string $args['type'] error type ''404' or 'module'
 * @param string $args['message'] custom error message
 * @return string HTML string
 */
function errors_user_main($args)
{
    $type = FormUtil::getPassedValue('errtype', isset($args['type']) ? $args['type'] : LogUtil::getErrorType(), 'GET');

    // create an output object
    $pnRender = Renderer::getInstance('Errors', false);

    // perform any error specific tasks
    $protocol = System::serverGetVar('SERVER_PROTOCOL');
    switch ($type) {
        case 301:
            header("{$protocol} 301 Moved Permanently");
            break;
        case 403:
            header("{$protocol} 403 Access Denied");
            break;
        case 404:
            header("{$protocol} 404 Not Found");
            break;
        case 500:
            header("{$protocol} 500 Internal Server Error");
        default:
    }

    // load the stylesheet
    PageUtil::addVar('stylesheet', 'system/Errors/style/style.css');

    // assign the document info
    $pnRender->assign('reportlevel', System::getVar('reportlevel'));
    $pnRender->assign('currenturi', pnGetCurrentURI());
    $pnRender->assign('localreferer', pnLocalReferer());
    $pnRender->assign('sitename', System::getVar('sitename'));
    $pnRender->assign('reportlevel', System::getVar('reportlevel'));
    $pnRender->assign('funtext', System::getVar('funtext'));

    // assign the list of registered errors
    $pnRender->assign('messages', LogUtil::getErrorMessages());

    // return the template output
    if ($pnRender->template_exists($template = "errors_user_{$type}.htm")) {
        return $pnRender->fetch($template);
    } else {
        return $pnRender->fetch('errors_user_main.htm');
    }
}

/**
 * display a system error
 *
 * @author Mark West
 */
function errors_user_system($args)
{
    $pnRender = Renderer::getInstance('Errors', false);
    $pnRender->assign($args);
    return $pnRender->fetch('errors_user_system.htm');
}
