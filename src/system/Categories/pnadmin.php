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
 * main admin function
 */
function Categories_admin_main()
{
    // Security check will be done in view()
    return Categories_admin_view();
}

/**
 * view categories
 */
function Categories_admin_view ()
{
     $layersMenuPath = 'javascript/phplayersmenu/lib';
     Loader::loadFile ('PHPLIB.php', $layersMenuPath);
     Loader::loadFile ('layersmenu-common.inc.php', $layersMenuPath);
     Loader::loadFile ('layersmenu.inc.php', $layersMenuPath);
     Loader::loadFile ('treemenu.inc.php', $layersMenuPath);

    $root_id = FormUtil::getPassedValue ('dr', 1);

    if (!SecurityUtil::checkPermission('Categories::category', "ID::$root_id", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    if (!SecurityUtil::checkPermission('Categories::category', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // disable attribution for performance
    $GLOBALS['pntables']['categories_category_db_extra_enable_attribution'] = false;
    $pntables    = pnDBGetTables ();
    $columnArray = array ('id', 'name', 'display_name', 'path');
    $cats        = CategoryUtil::getSubCategories ($root_id, true, true, true, true, true, '', '', null, $columnArray);
    $menuTxt     = CategoryUtil::getCategoryTreeJS ($cats);
    $GLOBALS['pntables']['categories_category_db_extra_enable_attribution'] = true;

    $pnRender = Renderer::getInstance('Categories', false);
    $pnRender->assign('menuTxt', $menuTxt);
    return $pnRender->fetch('categories_admin_view.htm');
}

/**
 * display configure module page
 */
function Categories_admin_config ()
{
    if (!SecurityUtil::checkPermission('Categories::', "::", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $pnRender = Renderer::getInstance('Categories', false);
    return $pnRender->fetch('categories_admin_config.htm');
}

/**
 * edit category
 */
function Categories_admin_edit ()
{
    $cid      = FormUtil::getPassedValue ('cid', 0);
    $root_id  = FormUtil::getPassedValue ('dr', 1);
    $mode     = FormUtil::getPassedValue ('mode', 'new');
    $allCats  = '';
    $editCat  = '';

    $languages = ZLanguage::getInstalledLanguages();

    // indicates that we're editing
    if ($mode == 'edit')
    {
        if (!SecurityUtil::checkPermission('Categories::category', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        if (!$cid) {
            return LogUtil::registerError(__('Error! Cannot determine valid \'cid\' for edit mode in \'Categories_admin_edit\'.'));
        }

        $category = new PNCategory();
        $editCat  = $category->select ($cid);
        if ($editCat == false) {
            return LogUtil::registerError(__('Sorry! No such item found.'), 404);
        }
    }
    else
    {
        // new category creation
        if (!SecurityUtil::checkPermission('Categories::category', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // since we inherit the domain settings from the parent, we get
        // the inherited (and merged) object from session
        if (isset($_SESSION['newCategory']) && $_SESSION['newCategory']) {
            $editCat = $_SESSION['newCategory'];
            unset ($_SESSION['newCategory']);
            $category = new PNCategory(); // need this for validation info
        }
        // if we're back from validation get the object from input
        elseif (FormUtil::getValidationErrors()) {
            $category = new PNCategory('V'); // need this for validation info
            $editCat  = $category->get ();
        }
        // someone just pressen 'new' -> populate defaults
        else {
            $category = new PNCategory(); // need this for validation info
            $editCat['sort_value'] = '0';
        }
    }

    $reloadOnCatChange = ($mode != 'edit');
    $allCats  = CategoryUtil::getSubCategories ($root_id, true, true, true, false, true);

    // now remove the categories which are below $editCat ...
    // you should not be able to set these as a parent category as it creates a circular hierarchy (see bug #4992)
    if (isset($editCat['ipath'])) {
        $cSlashEdit = StringUtil::countInstances ($editCat['ipath'], '/');
        foreach ($allCats as $k=>$v) {
            $cSlashCat = StringUtil::countInstances ($v['ipath'], '/');
            if ($cSlashCat >= $cSlashEdit && strpos ($v['ipath'], $editCat['ipath']) !== false) {
                unset ($allCats[$k]);
            }
        }
    }

    $selector = CategoryUtil::getSelector_Categories ($allCats, 'id', (isset($editCat['parent_id']) ? $editCat['parent_id'] : 0), 'category[parent_id]', isset($defaultValue) ? $defaultValue : null, null, $reloadOnCatChange);

    $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : array();

    $pnRender = Renderer::getInstance('Categories', false);
    $pnRender->assign('mode', $mode);
    $pnRender->assign('category', $editCat);
    $pnRender->assign('attributes', $attributes);
    $pnRender->assign('languages', $languages);
    $pnRender->assign('categorySelector', $selector);
    $pnRender->assign('validation', $category->_objValidation);

    if ($mode == 'edit') {
        $pnRender->assign('haveSubcategories', CategoryUtil::haveDirectSubcategories ($cid));
        $pnRender->assign('haveLeafSubcategories', CategoryUtil::haveDirectSubcategories ($cid, false, true));
    }

    return $pnRender->fetch('categories_admin_edit.htm');
}

function Categories_admin_editregistry ()
{
    if (!SecurityUtil::checkPermission('Categories::', "::", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $root_id  = FormUtil::getPassedValue ('dr', 1);
    $id       = FormUtil::getPassedValue ('id', 0);
    $ot       = FormUtil::getPassedValue ('ot', 'category_registry');

    if (!($class = Loader::loadClassFromModule ('Categories', $ot))) {
        return pn_exit ("Unable to load class [$ot] ...");
    }
    if (!($arrayClass = Loader::loadArrayClassFromModule ('Categories', $ot))) {
        return pn_exit ("Unable to load class [$ot] ...");
    }

    $obj  = new $class ();
    $data = $obj->getDataFromInput ();
    if (!$data) {
        $data = $obj->getFailedValidationData ();
        if (!$data) {
            $data = array();
        }
    }

    $where    = '';
    $sort     = 'crg_modname, crg_property';
    $objArray = new $arrayClass ();
    $dataA    = $objArray->get($where, $sort);

    $pnRender = Renderer::getInstance('Categories', false);
    $pnRender->assign('objectArray', $dataA);
    $pnRender->assign('newobj', $data);
    $pnRender->assign('root_id', $root_id);
    $pnRender->assign('id', $id);
    $pnRender->assign('validation', $obj->_objValidation);

    return $pnRender->fetch('categories_admin_registry_edit.htm');
}

/**
 * display new category form
 */
function Categories_admin_new ()
{
    $_POST['mode'] = 'new';
    return Categories_admin_edit ();
}

/**
 * generic function to handle copy, delete and move operations
 */
function Categories_admin_op ()
{
    $cid      = FormUtil::getPassedValue ('cid', 1);
    $root_id  = FormUtil::getPassedValue ('dr', 1);
    $op       = FormUtil::getPassedValue ('op', 'NOOP');

    if (!SecurityUtil::checkPermission('Categories::category', "ID::$cid", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    $category = new PNCategory();
    $category    = $category->select ($cid);
    $subCats     = CategoryUtil::getSubCategories ($cid, false, false);
    $allCats     = CategoryUtil::getSubCategories ($root_id, true, true, true, false, true, $cid);
    $selector    = CategoryUtil::getSelector_Categories ($allCats);

    $pnRender = Renderer::getInstance('Categories');
    $pnRender->caching = false;
    $pnRender->assign('category', $category);
    $pnRender->assign('numSubcats', count($subCats));
    $pnRender->assign('categorySelector', $selector);

    $tplName = 'categories_admin_' . $op . '.htm';
    return $pnRender->fetch($tplName);
}

/**
 * global module preferences
 */
function Categories_admin_preferences()
{
    if (!SecurityUtil::checkPermission('Categories::preferences', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $pnRender = Renderer::getInstance('Categories', false);
    $pnRender->assign ('userrootcat', ModUtil::getVar('Categories', 'userrootcat', '/__SYSTEM__'));
    $pnRender->assign ('allowusercatedit', ModUtil::getVar('Categories', 'allowusercatedit', 0));
    $pnRender->assign ('autocreateusercat', ModUtil::getVar('Categories', 'autocreateusercat', 0));
    $pnRender->assign ('autocreateuserdefaultcat', ModUtil::getVar('Categories', 'autocreateuserdefaultcat', 0));
    $pnRender->assign ('userdefaultcatname', ModUtil::getVar('Categories', 'userdefaultcatname', 0));
    $pnRender->assign ('permissionsall', ModUtil::getVar('Categories', 'permissionsall', 0));

    return $pnRender->fetch('categories_admin_preferences.htm');
}

