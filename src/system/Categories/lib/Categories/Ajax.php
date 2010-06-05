<?php

/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Permissions
 */
class Categories_Ajax extends AbstractController {

    /**
     * Resequence categories
     *
     */
    public function resequence() {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }
        $data = json_decode(FormUtil::getPassedValue('data', null, 'post'), true);
        $cats = CategoryUtil::getSubCategories(1, true, true, true, true, true, '', 'id');

        foreach ($cats as $id => $cat) {
            if(isset($data[$id])) {
                $cats[$id]['sort_value'] = $data[$id]['lineno'];
                $cats[$id]['parent_id'] = $data[$id]['parent'];
                $obj = new Categories_DBObject_Category($cats[$id]);
                $obj->update();
            }
        }

        return array('result' => true);
    }

}