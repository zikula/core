<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


class Categories_Ajax extends Zikula_Controller {

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