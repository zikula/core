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
            AjaxUtil::error($this->__('Sorry! You have not been granted access to this page.'));
        }
        if (!SecurityUtil::confirmAuthKey()) {
            AjaxUtil::error($this->__("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
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