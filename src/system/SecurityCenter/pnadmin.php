<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
*/

/**
 * the main administration function
 * This function is the default function, and is called whenever the
 * module is initiated without defining arguments.  As such it can
 * be used for a number of things, but most commonly it either just
 * shows the module menu and returns or calls whatever the module
 * designer feels should be the default function (often this is the
 * view() function)
 * @return string HTML string
 */
function securitycenter_admin_main()
{
    // Security check will be done in view()
    return securitycenter_admin_view();
}

/**
 * delete item
 * This is a standard function that is called whenever an administrator
 * wishes to delete a current module item.  Note that this function is
 * the equivalent of both of the modify() and update() functions above as
 * it both creates a form and processes its output.  This is fine for
 * simpler functions, but for more complex operations such as creation and
 * modification it is generally easier to separate them into separate
 * functions.  There is no requirement in the Zikula MDG to do one or the
 * other, so either or both can be used as seen appropriate by the module
 * developer
 * @param int $args['hid'] the id of the item to be deleted
 * @param bool $args['confirmation'] confirmation that this item can be deleted
 * @return mixed HTML string if no confirmation, true if successful, false otherwise
 */
function securitycenter_admin_delete($args)
{
    $hid = FormUtil::getPassedValue('hid', isset($args['hid']) ? $args['hid'] : null, 'REQUEST');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
    if (!empty($objectid)) {
        $hid = $objectid;
    }

    // Get the current item
    $item = pnModAPIFunc('SecurityCenter', 'user', 'get', array('hid' => $hid));

    if ($item == false) {
        return LogUtil::registerError(__('Sorry! No such item found.'), 404);
    }

    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', "$item[hid]::$item[hacktime]", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet - display a suitable form to obtain confirmation
        // of this action from the user
        $pnRender = Renderer::getInstance('SecurityCenter', false);
        $pnRender->assign('hid', $hid);
        return $pnRender->fetch('securitycenter_admin_delete.htm');
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code.
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('SecurityCenter','admin','view'));
    }

    // Call the API to delete the item
    if (pnModAPIFunc('SecurityCenter', 'admin', 'delete', array('hid' => $hid))) {
        // Success
        LogUtil::registerStatus(__('Done! Deleted it.'));
    }

    return pnRedirect(pnModURL('SecurityCenter', 'admin', 'view'));
}

/**
 * view items in db
 * @param int $startnum number of item to start view from
 * @return string HTML string
 */
function securitycenter_admin_view($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');

    // Create output object
    $pnRender = Renderer::getInstance('SecurityCenter', false);

    // Get all items
    $items = pnModAPIFunc('SecurityCenter', 'user', 'getall',
                          array('startnum' => $startnum,
                                'numitems' => pnModGetVar('SecurityCenter', 'itemsperpage')));

    $hackattempts = array();
    if ($items) {
        foreach ($items as $item) {

            // Get the full item
            $fullitem = pnModAPIFunc('SecurityCenter', 'user', 'get', array('hid' => $item['hid']));

            $fullitem['hacktime'] = DateUtil::strftime(__('%b %d, %Y - %I:%M %p'), $fullitem['hacktime']);
            if ($fullitem['userid'] == 0) {
                $fullitem['userid'] = pnConfigGetVar('anonymous');
            } else {
                $fullitem['userid'] = pnUserGetVar('uname', $fullitem['userid']);
            }

            // Add users options for the item.
            $options = array();
            if (SecurityUtil::checkPermission('SecurityCenter::', "$item[hid]::$item[hacktime]", ACCESS_EDIT)) {
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'browserinfo')),
                                   'title' => __('Browser information list'));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'requestarray')),
                                   'title' => __("View 'request' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'getarray')),
                                   'title' => __("View 'get' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'postarray')),
                                   'title' => __("View 'post' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'serverarray')),
                                   'title' => __("View 'server' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'envarray')),
                                   'title' => __("View 'env' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'cookiearray')),
                                   'title' => __("View 'cookie' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'filesarray')),
                                   'title' => __("View 'files' array"));
                $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'display', array('hid' => $item['hid'], 'arraytype' => 'sessionarray')),
                                   'title' => __("View 'session' array"));
                if (SecurityUtil::checkPermission('SecurityCenter::', "$item[hid]::$item[hacktime]", ACCESS_DELETE)) {
                    $options[] = array('url' => pnModURL('SecurityCenter', 'admin', 'delete', array('hid' => $item['hid'])),
                                       'title' => __('Delete'));
                }
                $fullitem['options'] = $options;
            }
            $hackattempts[] = $fullitem;
        }
    }
    $pnRender->assign('hackattempts', $hackattempts);

    // Assign the values for the smarty plugin to produce a pager.
    $pnRender->assign('pager', array('numitems' => pnModAPIFunc('SecurityCenter', 'user', 'countitems'),
                                     'itemsperpage' => pnModGetVar('SecurityCenter', 'itemsperpage')));

    return $pnRender->fetch('securitycenter_admin_view.htm');
}

/**
 * This is a standard function to modify the configuration parameters of the
 * module
 * @return string HTML string
 */
function securitycenter_admin_modifyconfig()
{
    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('SecurityCenter', false);

    $pnRender->assign('itemsperpage', pnModGetVar('SecurityCenter', 'itemsperpage'));

    // assign all of our vars
    $vars = pnModGetVar(PN_CONFIG_MODULE);
    $pnRender->assign($vars);

    // Return the output that has been generated by this function
    return $pnRender->fetch('securitycenter_admin_modifyconfig.htm');
}

/**
 * This is a standard function to update the configuration parameters of the
 * module given the information passed back by the modification form
 * @see securitycenter_admin_modifyconfig()
 * @param int enableanticracker
 * @param int itemsperpage
 * @param int emailhackattempt
 * @param int loghackattempttodb
 * @param int onlysendsummarybyemail
 * @param int updatecheck
 * @param int updatefrequency
 * @param int keyexpiry
 * @param int sessionauthkeyua
 * @param string secure_domain
 * @param int signcookies
 * @param string signingkey
 * @param string seclevel
 * @param int secmeddays
 * @param int secinactivemins
 * @param int sessionstoretofile
 * @param string sessionsavepath
 * @param int gc_probability
 * @param int anonymoussessions
 * @param int sessionrandregenerate
 * @param int sessionregenerate
 * @param int sessionregeneratefreq
 * @param int sessionipcheck
 * @param string sessionname
 * @param int filtergetvars
 * @param int filterpostvars
 * @param int filtercookievars
 * @param int outputfilter
 * @param string summarycontent
 * @param string fullcontent
 * @return bool true if successful, false otherwise
 * @todo documement parameters
 */
function securitycenter_admin_updateconfig()
{
    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code.
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('SecurityCenter','admin','view'));
    }

    // Update module variables.
    $enableanticracker = (int)FormUtil::getPassedValue('enableanticracker', 0, 'POST');
    pnConfigSetVar('enableanticracker', $enableanticracker);

    $itemsperpage = (int)FormUtil::getPassedValue('itemsperpage', 10, 'POST');
    pnModSetVar('SecurityCenter', 'itemsperpage', $itemsperpage);

    $emailhackattempt = (int)FormUtil::getPassedValue('emailhackattempt', 0, 'POST');
    pnConfigSetVar('emailhackattempt', $emailhackattempt);

    $loghackattempttodb = (int)FormUtil::getPassedValue('loghackattempttodb', 0, 'POST');
    pnConfigSetVar('loghackattempttodb', $loghackattempttodb);

    $onlysendsummarybyemail = (int)FormUtil::getPassedValue('onlysendsummarybyemail', 0, 'POST');
    pnConfigSetVar('onlysendsummarybyemail', $onlysendsummarybyemail);

    $updatecheck = (int)FormUtil::getPassedValue('updatecheck', 0, 'POST');
    pnConfigSetVar('updatecheck', $updatecheck);

    // if update checks are disabled, reset values to force new update check if re-enabled
    if ($updatecheck == 0) {
        pnConfigSetVar('updateversion', PN_VERSION_NUM);
        pnConfigSetVar('updatelastchecked', 0);
    }

    $updatefrequency = (int)FormUtil::getPassedValue('updatefrequency', 30, 'POST');
    pnConfigSetVar('updatefrequency', $updatefrequency);

    $keyexpiry = (int)FormUtil::getPassedValue('keyexpiry', 0, 'POST');
    if ($keyexpiry < 0 || $keyexpiry > 3600) {
        $keyexpiry = 0;
    }
    pnConfigSetVar('keyexpiry', $keyexpiry);

    $sessionauthkeyua = (int)FormUtil::getPassedValue('sessionauthkeyua', 0, 'POST');
    pnConfigSetVar('sessionauthkeyua', $sessionauthkeyua);

    $secure_domain = FormUtil::getPassedValue('secure_domain', '', 'POST');
    pnConfigSetVar('secure_domain', $secure_domain);

    $signcookies = (int)FormUtil::getPassedValue('signcookies', 1, 'POST');
    pnConfigSetVar('signcookies', $signcookies);

    $signingkey = FormUtil::getPassedValue('signingkey', '', 'POST');
    pnConfigSetVar('signingkey', $signingkey);

    $seclevel = FormUtil::getPassedValue('seclevel', 'High', 'POST');
    pnConfigSetVar('seclevel', $seclevel);

    $secmeddays = (int)FormUtil::getPassedValue('secmeddays', 7, 'POST');
    if ($secmeddays < 1 || $secmeddays > 365) {
        $secmeddays = 7;
    }
    pnConfigSetVar('secmeddays', $secmeddays);

    $secinactivemins = (int)FormUtil::getPassedValue('secinactivemins', 20, 'POST');
    if ($secinactivemins < 1 || $secinactivemins > 1440) {
        $secinactivemins = 7;
    }
    pnConfigSetVar('secinactivemins', $secinactivemins);

    $sessionstoretofile = (int)FormUtil::getPassedValue('sessionstoretofile', 0, 'POST');
    $sessionsavepath = FormUtil::getPassedValue('sessionsavepath', '', 'POST');

    // check session path config is writable (if method is being changed to session file storage)
    $cause_logout = false;
    $storeTypeCanBeWritten = true;
    if ($sessionstoretofile == 1 && !empty($sessionsavepath)) {
        // fix path on windows systems
        $sessionsavepath = str_replace('\\', '/', $sessionsavepath);
        // sanitize the path
        $sessionsavepath = trim(stripslashes($sessionsavepath));

        // check if sessionsavepath is a dir and if it is writable
        // if yes, we need to logout
        $cause_logout = (is_dir($sessionsavepath)) ? is_writable($sessionsavepath) : false;

        if ($cause_logout == false) {
            // an error occured - we do not change the way of storing session data
            LogUtil::registerStatus(__('Error! Session path not writeable!'));
            $storeTypeCanBeWritten = false;
        }
    }
    if ($storeTypeCanBeWritten == true) {
        pnConfigSetVar('sessionstoretofile', $sessionstoretofile);
        pnConfigSetVar('sessionsavepath', $sessionsavepath);
    }

    if ((bool)$sessionstoretofile != (bool)pnConfigGetVar('sessionstoretofile')) {
        // logout if going from one storage to another one
        $cause_logout = true;
    }

    $gc_probability = (int)FormUtil::getPassedValue('gc_probability', 100, 'POST');
    if ($gc_probability < 1 || $gc_probability > 10000) {
        $gc_probability = 7;
    }
    pnConfigSetVar('gc_probability', $gc_probability);

    $anonymoussessions = (int)FormUtil::getPassedValue('anonymoussessions', 1, 'POST');
    pnConfigSetVar('anonymoussessions', $anonymoussessions);

    $sessionrandregenerate = (int)FormUtil::getPassedValue('sessionrandregenerate', 1, 'POST');
    pnConfigSetVar('sessionrandregenerate', $sessionrandregenerate);

    $sessionregenerate = (int)FormUtil::getPassedValue('sessionregenerate', 1, 'POST');
    pnConfigSetVar('sessionregenerate', $sessionregenerate);

    $sessionregeneratefreq = (int)FormUtil::getPassedValue('sessionregeneratefreq', 10, 'POST');
    if ($sessionregeneratefreq < 1 || $sessionregeneratefreq > 100) {
        $sessionregeneratefreq = 10;
    }
    pnConfigSetVar('sessionregeneratefreq', $sessionregeneratefreq);

    $sessionipcheck = (int)FormUtil::getPassedValue('sessionipcheck', 0, 'POST');
    pnConfigSetVar('sessionipcheck', $sessionipcheck);

    $sessionname = FormUtil::getPassedValue('sessionname', 'ZSID', 'POST');
    if (strlen($sessionname) < 3) {
        $sessionname = 'ZSID';
    }

    // cause logout if we changed session name
    if ($sessionname != pnConfigGetVar('sessionname')) {
        $cause_logout = true;
    }

    pnConfigSetVar('sessionname', $sessionname);
    pnConfigSetVar('sessionstoretofile', $sessionstoretofile);


    $filtergetvars = FormUtil::getPassedValue('filtergetvars', 1, 'POST');
    pnConfigSetVar('filtergetvars', $filtergetvars);

    $filterpostvars = FormUtil::getPassedValue('filterpostvars', 0, 'POST');
    pnConfigSetVar('filterpostvars', $filterpostvars);

    $filtercookievars = FormUtil::getPassedValue('filtercookievars', 1, 'POST');
    pnConfigSetVar('filtercookievars', $filtercookievars);

    $outputfilter = FormUtil::getPassedValue('outputfilter', 0, 'POST');
    pnConfigSetVar('outputfilter', $outputfilter);

    $useids = (bool)FormUtil::getPassedValue('useids', 0, 'POST');
    pnConfigSetVar('useids', $useids);

    // create tmp directory for PHPIDS
    if ($useids == 1) {
        $idsTmpDir = CacheUtil::getLocalDir() . '/idsTmp';
        if (!file_exists($idsTmpDir)) {
            CacheUtil::clearLocalDir('idsTmp');
        }
    }

    $idsfilter = FormUtil::getPassedValue('idsfilter', 'xml', 'POST');
    pnConfigSetVar('idsfilter', $idsfilter);

    $idsimpactthresholdone = (int)FormUtil::getPassedValue('idsimpactthresholdone', 1, 'POST');
    pnConfigSetVar('idsimpactthresholdone', $idsimpactthresholdone);

    $idsimpactthresholdtwo = (int)FormUtil::getPassedValue('idsimpactthresholdtwo', 10, 'POST');
    pnConfigSetVar('idsimpactthresholdtwo', $idsimpactthresholdtwo);

    $idsimpactthresholdthree = (int)FormUtil::getPassedValue('idsimpactthresholdthree', 25, 'POST');
    pnConfigSetVar('idsimpactthresholdthree', $idsimpactthresholdthree);

    $idsimpactthresholdfour = (int)FormUtil::getPassedValue('idsimpactthresholdfour', 75, 'POST');
    pnConfigSetVar('idsimpactthresholdfour', $idsimpactthresholdfour);

    $idsimpactmode = (int) FormUtil::getPassedValue('idsimpactmode', 1, 'POST');
    pnConfigSetVar('idsimpactmode', $idsimpactmode);



    // to do set some defaults here possibly read default content from file
    // so it's not repeated in code - markwest
    $summarycontent = FormUtil::getPassedValue('summarycontent', '', 'POST');
    pnConfigSetVar('summarycontent', $summarycontent);
    $fullcontent = FormUtil::getPassedValue('fullcontent', '', 'POST');
    pnConfigSetVar('fullcontent', $fullcontent);

    // Let any other modules know that the modules configuration has been updated
    pnModCallHooks('module','updateconfig', 'SecurityCenter', array('module' => 'SecurityCenter'));

    // clear all cache and compile directories
    pnModAPIFunc('Settings', 'admin', 'clearallcompiledcaches');

    // the module configuration has been updated successfuly
    LogUtil::registerStatus(__('Done! Saved module configuration.'));

    // we need to auto logout the user if they changed from DB to FILE
    if ($cause_logout == true) {
        pnUserLogOut();
        return pnRedirect(pnModURL('Users', 'user', 'loginscreen'));
    }

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
    return pnRedirect(pnModURL('SecurityCenter','admin', 'main'));
}

/**
 * output contents of http array
 * @param int $args['hid'] hack id
 * @param string $args['arraytype'] type of array to output
 * @return string HTML output string
*/
function securitycenter_admin_display($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    $hid = FormUtil::getPassedValue('hid', isset($args['hid']) ? $args['hid'] : null, 'GET');
    $arraytype = FormUtil::getPassedValue('arraytype', isset($args['arraytype']) ? $args['arraytype'] : null, 'GET');

    if (empty($hid) ||
        empty($arraytype)) {
        return LogUtil::registerArgsError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('SecurityCenter', false);

    // assign the page title
    $pnRender->assign('title', strtoupper($arraytype));

    // Get the item from our API
    $item = pnModAPIFunc('SecurityCenter', 'user', 'get', array('hid' => $hid));
    // extract the data we serialised in the db
    $variablearray = unserialize($item[$arraytype]);

    $arrayvariables = array();
    if (is_array($variablearray)) {
        // output the variables into the table
        while (list ($key, $value) = each($variablearray)) {
            $arrayvariables[] = array('key' => $key, 'value' => $value);
        }
    }
    $pnRender->assign('arrayvariables', $arrayvariables);

    return $pnRender->fetch('securitycenter_admin_display.htm');
}

/**
 * Generic view function - used for log events
 * @return string HTML output string
 */
function securitycenter_admin_viewobj()
{
    // Security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    $ot       = FormUtil::getPassedValue('ot', 'log_event', 'GETPOST');
    $sort     = FormUtil::getPassedValue('sort', 'lge_date DESC', 'GETPOST');
    if ($ot == 'intrusion') {
        $filterdefault = array('uid' => null, 'name' => null, 'tag' => null, 'value' => null, 'page' => null, 'ip' => null, 'impact' => null);
    } else {
        $filterdefault = array('uid' => null, 'component' => null, 'module' => null, 'type' => null);
    }
    $filter   = FormUtil::getPassedValue('filter', $filterdefault, 'GETPOST');
    $startnum = (int)FormUtil::getPassedValue('startnum', 0, 'GET');
    $pagesize = (int)pnModGetVar('SecurityCenter', 'pagesize', 25);


    // load array class
    if (!($class = Loader::loadArrayClassFromModule('SecurityCenter', $ot, true)))
        return pn_exit(__f('Unable to load class [%s] ...', DataUtil::formatForDisplay($ot)));

    // instantiate object, generate where clause and select
    $objArray = new $class();
    $where = $objArray->genFilter();
    $data  = $objArray->get($where, '', $startnum, $pagesize);

    // Create output object
    $pnRender = Renderer::getInstance('SecurityCenter', false);
    $pnRender->assign('ot', $ot);
    $pnRender->assign('filter', $filter);
    $pnRender->assign('objectArray', $data);

    // Assign the values for the smarty plugin to produce a pager.
    $pager = array();
    $pager['numitems']     = $objArray->getCount($where);
    $pager['itemsperpage'] = $pagesize;
    $pnRender->assign('startnum', $startnum);
    $pnRender->assign('pager', $pager);

    // fetch output from template
    return $pnRender->fetch('securitycenter_admin_view_' . DataUtil::formatForOS($ot) . '.htm');
}

/**
 * display the allowed html form
 *
 * @author Zikula development team
 * @return string html output
 */
function securitycenter_admin_allowedhtml($args)
{
    // security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // create a new output object
    $pnRender = Renderer::getInstance('SecurityCenter', false);

    $pnRender->assign('htmltags', _securitycenter_admin_gethtmltags());
    $pnRender->assign('currenthtmltags', pnConfigGetVar('AllowableHTML'));
    $pnRender->assign('htmlentities', pnConfigGetVar('htmlentities'));

    // check for HTML Purifier outputfilter
    $htmlpurifier = (bool) (pnConfigGetVar('outputfilter') == 1);
    $pnRender->assign('htmlpurifier', $htmlpurifier);
    $pnRender->assign('configurl', pnModURL('SecurityCenter', 'admin', 'modifyconfig'));

    return $pnRender->fetch('securitycenter_admin_allowedhtml.htm');
}

/**
 * update allowed html settings
 *
 * @author Zikula development team
 * @return mixed true if successful, false if unsuccessful, error string otherwise
 */
function securitycenter_admin_updateallowedhtml($args)
{
    // security check
    if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // confirm the forms auth key
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError();
    }

    // update the allowed html settings
    $allowedhtml = array();
    $htmltags = _securitycenter_admin_gethtmltags();
    foreach ($htmltags as $htmltag) {
        $tagval = (int) FormUtil::getPassedValue('htmlallow' . $htmltag . 'tag', 0, 'POST');
        if (($tagval != 1) && ($tagval != 2)) {
            $tagval = 0;
        }
        $allowedhtml[$htmltag] = $tagval;
    }
    pnConfigSetVar('AllowableHTML', $allowedhtml);

    // one additonal config var is set on this page
    $htmlentities = FormUtil::getPassedValue('xhtmlentities', 0, 'POST');
    pnConfigSetVar('htmlentities', $htmlentities);

    // clear all cache and compile directories
    pnModAPIFunc('Settings', 'admin', 'clearallcompiledcaches');

    // all done successfully
    LogUtil::registerStatus(__('Done! Saved module configuration.'));

    return pnRedirect(pnModURL('SecurityCenter', 'admin', 'allowedhtml'));
}

/**
 * utility function to return the list of available tags
 *
 * @access private
 * @return string html output
 */
function _securitycenter_admin_gethtmltags()
{
    // Possible allowed HTML tags
    return array('!--',
                  'a',
                  'abbr',
                  'acronym',
                  'address',
                  'applet',
                  'area',
                  'b',
                  'base',
                  'basefont',
                  'bdo',
                  'big',
                  'blockquote',
                  'br',
                  'button',
                  'caption',
                  'center',
                  'cite',
                  'code',
                  'col',
                  'colgroup',
                  'del',
                  'dfn',
                  'dir',
                  'div',
                  'dl',
                  'dd',
                  'dt',
                  'em',
                  'embed',
                  'fieldset',
                  'font',
                  'form',
                  'h1',
                  'h2',
                  'h3',
                  'h4',
                  'h5',
                  'h6',
                  'hr',
                  'i',
                  'iframe',
                  'img',
                  'input',
                  'ins',
                  'kbd',
                  'label',
                  'legend',
                  'li',
                  'map',
                  'marquee',
                  'menu',
                  'nobr',
                  'object',
                  'ol',
                  'optgroup',
                  'option',
                  'p',
                  'param',
                  'pre',
                  'q',
                  's',
                  'samp',
                  'script',
                  'select',
                  'small',
                  'span',
                  'strike',
                  'strong',
                  'sub',
                  'sup',
                  'table',
                  'tbody',
                  'td',
                  'textarea',
                  'tfoot',
                  'th',
                  'thead',
                  'tr',
                  'tt',
                  'u',
                  'ul',
                  'var');
}
