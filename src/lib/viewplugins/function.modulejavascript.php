<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View function to include module specific javascripts
 *
 * Available parameters:
 *  - modname     module name (if not set, the current module is assumed)
 *                if modname="" than we will look into the main javascript folder
 *  - script      name of the external javascript file (mandatory)
 *  - modonly     javascript will only be included when the the current module is $modname
 *  - onload      function to be called with onLoad handler in body tag, makes sense with assign set only, see example #2
 *  - assign      if set, the tag and the script filename are returned
 *
 * Example: {modulejavascript modname=foobar script=module_admin_config.js modonly=1 }
 * Output:  <script type="text/javascript" src="modules/foobar/javascript/module_admin_config.js">
 *
 * Example: {modulejavascript modname=foobar script=module_admin_config.js modonly=1 onload="dosomething()" assign=myjs }
 * Output: nothing, but assigns a variable containing several values:
 *      $myjs.scriptfile = "modules/foobar/javascript/module_admin_config.js"
 *      $myjs.tag = "<script type=\"text/javascript\" src=\"modules/foobar/javascript/module_admin_config.js\"></script>"
 *      $myjs.onload = "onLoad=\"dosomething()\"";
 *      Possible code in master.tpl would be:
 *
 *      ...
 *      { $myjs.tag }
 *      </head>
 *      <body { $myjs.onload } >
 *      ...
 *
 *      which results in
 *
 *      ...
 *      <script type="text/javascript" src="modules/foobar/javascript/module_admin_config.js"></script>
 *      </head>
 *      <body onLoad="dosomething()" >
 *      ...
 *
 *      if foobar is the current module.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The tag.
 */
function smarty_function_modulejavascript($params, Zikula_View $view)
{
    // check if script is set (mandatory)
    if (!isset($params['script'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modulejavascript', 'script')));
        return false;
    }

    // check if modname is set and if not, if $modonly is set
    if (!isset($params['modname'])) {
        if (isset($params['modonly'])) {
            // error - we want $modonly only with $modname
            $view->trigger_error(__f('Error! in %1$s: parameter \'%2$s\' only supported together with \'%3$s\' set.', array('modulejavascript', 'modonly', 'modname')));
            return;
        }
        // we use the current module name
        $params['modname'] = ModUtil::getName();
    }

    if (isset($params['modonly']) && ($params['modname'] <> ModUtil::getName())) {
        // current module is not $modname - do nothing and return silently
        return;
    }

    // if modname is empty, we will search the main javascript folder
    if ($params['modname'] == '') {
        $searchpaths = array('javascript', 'javascript/ajax');
    } else {
        // theme directory
        $theme         = DataUtil::formatForOS(UserUtil::getTheme());
        $osmodname     = DataUtil::formatForOS($params['modname']);
        $themepath     = "themes/$theme/javascript/$osmodname";

        // module directory
        $modinfo       = ModUtil::getInfoFromName($params['modname']);
        $osmoddir      = DataUtil::formatForOS($modinfo['directory']);
        $modpath       = "modules/$osmoddir/javascript";
        $syspath       = "system/$osmoddir/javascript";
        $modpathOld       = "modules/$osmoddir/pnjavascript";
        $syspathOld       = "system/$osmoddir/pnjavascript";

        $searchpaths = array( $themepath, $modpath, $syspath, $modpathOld, $syspathOld);
    }
    $osscript = DataUtil::formatForOS($params['script']);

    // search for the javascript
    $scriptsrc = '';
    foreach ($searchpaths as $path) {
        if (is_readable("$path/$osscript")) {
            $scriptsrc = "$path/$osscript";
            break;
        }
    }

    // if no module javascript has been found then return no content
    $tag = (empty($scriptsrc)) ? '' : '<script type="text/javascript" src="' . $scriptsrc . '"></script>';

    // onLoad event handler used?
    $onload = (isset($params['onload'])) ? 'onLoad="' . $params['onload'] . '"' : '';

    if (isset($params['assign'])) {
        $return = array();
        $return['scriptfile'] = $scriptsrc;
        $return['tag']        = $tag;
        $return['onload']     = $onload;
        $view->assign($params['assign'], $return);
    } else {
        return $tag;
    }
}
