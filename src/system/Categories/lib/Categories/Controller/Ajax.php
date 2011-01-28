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


class Categories_Controller_Ajax extends Zikula_Controller
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }

    /**
     * Resequence categories
     *
     */
    public function resequence() {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }
        $data = json_decode(FormUtil::getPassedValue('data', null, 'post'), true);
        $cats = CategoryUtil::getSubCategories(1, true, true, true, true, true, '', 'id');

        foreach ($cats as $k => $cat) {
            $cid = $cat['id'];
            if(isset($data[$cid])) {
                $cats[$k]['sort_value'] = $data[$cid]['lineno'];
                $cats[$k]['parent_id'] = $data[$cid]['parent'];
                $obj = new Categories_DBObject_Category($cats[$k]);
                $obj->update();
            }
        }

        $output['response'] = true;
        return new Zikula_Response_Ajax($output);
    }

}
