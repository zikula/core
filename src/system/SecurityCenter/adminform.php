<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnadmin.php 20346 2006-10-19 15:00:24Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
 */

class SecurityCenter_adminform extends AbstractController
{
    /**
     * Generic delete function for object model
     */
    public function delete()
    {
        // Security check
        if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // verify auth-key
        if (!SecurityUtil::confirmAuthKey('SecurityCenter')) {
            return LogUtil::registerAuthidError();
        }

        // get paramters
        $ot = FormUtil::getPassedValue('ot', 'log_event', 'GETPOST');
        $id = (int)FormUtil::getPassedValue('id', 0, 'GETPOST');

        // sanity checkc
        if (!is_numeric($id)) {
            return LogUtil::registerError($this->__f("Error! Received a non-numeric object ID '%s'.", $id));
        }

        // load class
        if (!($class = Loader::loadClassFromModule('SecurityCenter', $ot))) {
            return pn_exit($this->__f('Unable to load class [%1$s] for module [%2$s]', array($ot, 'SecurityCenter')));
        }

        // instantiate object, fetch object
        $object = new $class();
        $data = $object->get($id);

        // check for valid object
        if (!$data) {
            return LogUtil::registerError($this->__f('Error! Invalid %s received.', "object ID [$id]"));
        } else {
            // delete object
            $object->delete();
        }

        // redirect back to view function
        return System::redirect(ModUtil::url('SecurityCenter', 'admin', 'viewobj', array('ot' => $ot)));
    }
}