<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */

Loader::loadClassFromModule ('Categories', 'category');

/**
 * update category
 */
function Categories_adminform_edit ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $args = array();

    if (FormUtil::getPassedValue('category_copy_x', null, 'POST')) {
        $args['op']  = 'copy';
        $args['cid'] = $_POST['category']['id'];
        return System::redirect(ModUtil::url('Categories', 'admin', 'op', $args));
    }

    if (FormUtil::getPassedValue('category_move_x', null, 'POST')) {
        $args['op']  = 'move';
        $args['cid'] = $_POST['category']['id'];
        return System::redirect(ModUtil::url('Categories', 'admin', 'op', $args));
    }

    if (FormUtil::getPassedValue('category_delete_x', null, 'POST')) {
        $args['op']  = 'delete';
        $args['cid'] = $_POST['category']['id'];
        return System::redirect(ModUtil::url('Categories', 'admin', 'op', $args));
    }

    if (FormUtil::getPassedValue('category_user_edit_x', null, 'POST')) {
        $_SESSION['category_referer'] = System::serverGetVar('HTTP_REFERER');
        $args['dr'] = $_POST['category']['id'];
        return System::redirect(ModUtil::url('Categories', 'user', 'edit', $args));
    }

    $cat = new PNCategory ();
    $data = $cat->getDataFromInput ();

    if (!$cat->validate('admin')) {
        $category = FormUtil::getPassedValue ('category', null, 'POST');
        $args['cid'] = $category['id'];
        $args['mode'] = 'edit';
        return System::redirect(ModUtil::url('Categories', 'admin', 'edit', $args));
    }

    $attributes = array();
    $values = FormUtil::getPassedValue('attribute_value', 'POST');
    foreach (FormUtil::getPassedValue('attribute_name', 'POST') as $index => $name)
    {
        if (!empty($name))
            $attributes[$name] = $values[$index];
    }

    $cat->setDataField('__ATTRIBUTES__', $attributes);

    // retrieve old category from DB
    $category = FormUtil::getPassedValue ('category', null, 'POST');
    $oldCat = new PNCategory ($cat->_GET_FROM_DB, $category['id']);

    // update new category data
    $cat->update ();

    // since a name change will change the object path, we must rebuild it here
    if ($oldCat->_objData['name'] != $cat->_objData['name']) {
        $obj = $cat->_objData;
        CategoryUtil::rebuildPaths ('path', 'name', $obj['id']);
    }

    $msg = __f('Done! Saved the %s category.', $oldCat->_objData['name']);
    LogUtil::registerStatus($msg);
    return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
}

/**
 * create category
 */
function Categories_adminform_new ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    $cat = new PNCategory ();
    $cat->getDataFromInput ();

    // submit button wasn't pressed -> category was chosen from dropdown
    // we now get the parent (security) category domains so we can inherit them
    if (!FormUtil::getPassedValue('category_submit_x', null, 'POST')) {
        $newCat = $_POST['category'];
        $pcID   = $newCat['parent_id'];

        $pCat = new PNCategory ();
        $parentCat = $pCat->get($pcID);

        //$newCat['security_domain'] = $parentCat['security_domain'];
        //for ($i=1; $i<=5; $i++) {
        //    $name = 'data' . $i . '_domain';
        //    $newCat[$name] = $parentCat[$name];
        //}

        $_SESSION['newCategory'] = $newCat;

        return System::redirect(ModUtil::url('Categories', 'admin', 'new') . '#top');
    }

    if (!$cat->validate('admin')) {
        return System::redirect(ModUtil::url('Categories', 'admin', 'new') . '#top');
    }

    $attributes = array();
    $values = FormUtil::getPassedValue('attribute_value', array(), 'POST');
    foreach (FormUtil::getPassedValue('attribute_name', array(), 'POST') as $index => $name)
    {
        if (!empty($name)) {
            $attributes[$name] = $values[$index];
        }
    }

    if ($attributes) {
        $cat->setDataField('__ATTRIBUTES__', $attributes);
    }

    $cat->insert ();
    // since the original insert can't construct the ipath (since
    // the insert id is not known yet) we update the object here.
    $cat->update ();

    $msg = __f('Done! Inserted the %s category.', $cat->_objData['name']);
    LogUtil::registerStatus($msg);
    return System::redirect(ModUtil::url('Categories', 'admin', 'main') . '#top');
}

/**
 * delete category
 */
function Categories_adminform_delete ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    if (FormUtil::getPassedValue('category_cancel', null, 'POST')) {
        return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
    }

    $cid = FormUtil::getPassedValue('cid', null, 'POST');
    $cat = new PNCategory ();
    $cat->get($cid);

    // delete subdirectories
    if ($_POST['subcat_action'] == 'delete') {
        $cat->delete (true);
    } elseif ($_POST['subcat_action'] == 'move') {
        // move subdirectories
        $cat->deleteMoveSubcategories ($_POST['category']['parent_id']);
    }

    $msg = __f('Done! Deleted the %s category.', $cat->_objData['name']);
    LogUtil::registerStatus($msg);
    return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
}

/**
 * copy category
 */
function Categories_adminform_copy ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    if (FormUtil::getPassedValue('category_cancel', null, 'POST')) {
        return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
    }

    $cid = FormUtil::getPassedValue('cid', null, 'POST');
    $cat = new PNCategory ();
    $cat->get($cid);

    $cat->copy ($_POST['category']['parent_id']);

    $msg = __f('Done! Copied the %s category.', $cat->_objData['name']);
    LogUtil::registerStatus($msg);
    return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
}

/**
 * move category
 */
function Categories_adminform_move ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    if (FormUtil::getPassedValue('category_cancel', null, 'POST')) {
        return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
    }

    $cid = FormUtil::getPassedValue('cid', null, 'POST');
    $cat = new PNCategory ();
    $cat->get($cid);
    $cat->move ($_POST['category']['parent_id']);

    $msg = __f('Done! Moved the %s category.', $cat->_objData['name']);
    LogUtil::registerStatus($msg);
    return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
}

/**
 * rebuild path structure
 */
function Categories_adminform_rebuild_paths ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    CategoryUtil::rebuildPaths ('path', 'name');
    CategoryUtil::rebuildPaths ('ipath', 'id');

    LogUtil::registerStatus(__('Done! Rebuilt the category paths.'));
    return System::redirect(ModUtil::url('Categories', 'admin', 'main'));
}

function Categories_adminform_editregistry ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $id = FormUtil::getPassedValue('id', 0);

    if (!($class = Loader::loadClassFromModule ('Categories', 'category_registry', false))) {
        return z_exit(__f('Unable to load class [%s] for module [%s]', array('category_registry', 'Categories')));
    }

    if (FormUtil::getPassedValue('mode', null, 'GET') == 'delete') {
        $obj = new $class();
        $obj->get ($id);
        $obj->delete ($id);

        LogUtil::registerStatus(__('Done! Deleted the category registry entry.'));
        return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
    }

    $args = array();
    if (!FormUtil::getPassedValue('category_submit', null, 'POST')) // got here through selector auto-submit
    {
        $obj  = new $class();
        $data = $obj->getDataFromInput ($id);
        $args['category_registry'] = $data;
        return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry', $args));
    }

    $obj = new $class();
    $obj->getDataFromInput ();

    if (!$obj->validate('admin')) {
        return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
    }

    $obj->save();
    LogUtil::registerStatus(__('Done! Saved the category registry entry.'));
    return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
}

function Categories_adminform_preferences ()
{
    if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $userrootcat = FormUtil::getPassedValue ('userrootcat', null);
    if ($userrootcat) {
        pnModSetVar ('Categories', 'userrootcat', $userrootcat);
    }

    $autocreateusercat = (int)FormUtil::getPassedValue ('autocreateusercat', 0);
    pnModSetVar ('Categories', 'autocreateusercat', $autocreateusercat);

    $allowusercatedit = (int)FormUtil::getPassedValue ('allowusercatedit', 0);
    pnModSetVar ('Categories', 'allowusercatedit', $allowusercatedit);

    $autocreateuserdefaultcat = FormUtil::getPassedValue ('autocreateuserdefaultcat', 0);
    pnModSetVar ('Categories', 'autocreateuserdefaultcat', $autocreateuserdefaultcat);

    $userdefaultcatname = FormUtil::getPassedValue ('userdefaultcatname', 'Default');
    pnModSetVar ('Categories', 'userdefaultcatname', $userdefaultcatname);

    $permissionsall = (int)FormUtil::getPassedValue ('permissionsall', 0);
    pnModSetVar ('Categories', 'permissionsall', $permissionsall);

    LogUtil::registerStatus(__('Done! Saved module configuration.'));
    return System::redirect(ModUtil::url('Categories', 'admin', 'preferences'));
}
