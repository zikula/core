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

class Errors_User extends Zikula_Controller
{
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
    public function main($args)
    {
        $type = FormUtil::getPassedValue('errtype', isset($args['type']) ? $args['type'] : LogUtil::getErrorType(), 'GET');

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

        $this->renderer->setCaching(false);
        
        // assign the document info
        $this->renderer->assign('reportlevel', System::getVar('reportlevel'))
                       ->assign('currenturi', System::getCurrentUri())
                       ->assign('localreferer', System::localReferer())
                       ->assign('sitename', System::getVar('sitename'))
                       ->assign('reportlevel', System::getVar('reportlevel'))
                       ->assign('funtext', System::getVar('funtext'));

        // assign the list of registered errors
        $this->renderer->assign('messages', LogUtil::getErrorMessages());

        // return the template output
        if ($this->renderer->template_exists($template = "errors_user_{$type}.tpl")) {
            return $this->renderer->fetch($template);
        } else {
            return $this->renderer->fetch('errors_user_main.tpl');
        }
    }

    /**
     * display a system error
     *
     * @author Mark West
     */
    public function system($args)
    {
        $this->renderer->setCaching(false);
        $this->renderer->assign($args);
        return $this->renderer->fetch('errors_user_system.tpl');
    }
}