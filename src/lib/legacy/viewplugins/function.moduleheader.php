<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Smarty function build module header in user content page.
 *
 * {moduleheader}
 *
 * Available parameters:
 *  modname    Module name to display header for (optional, defaults to current module)
 *  type       Type for module links (defaults to 'user')
 *  title      Title to display in header (optional, defaults to module name)
 *  titlelink  Link to attach to title (optional, defaults to none)
 *  setpagetitle If set to true, {pagesetvar} is used to set page title
 *  insertstatusmsg If set to true, {insert name='getstatusmsg'} is put in front of template
 *  menufirst  If set to true, menu is first, then title
 *  putimage   If set to true, module image is also displayed next to title
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string A formatted string containing navigation for the module admin panel
 */
function smarty_function_moduleheader($params, $view)
{
    if (!isset($params['modname']) || !ModUtil::available($params['modname'])) {
        $params['modname'] = ModUtil::getName();
    }
    if (empty($params['modname'])) {
        return false;
    }
    $type = isset($params['type']) ? $params['type'] : 'user';
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $menufirst = isset($params['menufirst']) ? $params['menufirst'] : false;
    $putimage = isset($params['putimage']) ? $params['putimage'] : false;
    $setpagetitle = isset($params['setpagetitle']) ? $params['setpagetitle'] : false;
    $insertstatusmsg = isset($params['insertstatusmsg']) ? $params['insertstatusmsg'] : false;
    $cutlenght = isset($params['cutlenght']) ? $params['cutlenght'] : 20;
    if ($putimage) {
        $image = isset($params['image']) ? $params['image'] : ModUtil::getModuleImagePath($params['modname']);
    } else {
        $image = '';
    }
    if (!isset($params['title'])) {
        $modinfo = ModUtil::getInfoFromName($params['modname']);
        if (isset($modinfo['displayname'])) {
            $params['title'] = $modinfo['displayname'];
        } else {
            $params['title'] = ModUtil::getName();
        }
    }
    $titlelink = isset($params['titlelink']) ? $params['titlelink'] : false;

    $renderer = Zikula_View::getInstance('Theme');
    $renderer->setCaching(Zikula_View::CACHE_DISABLED);

    $renderer->assign('userthemename', UserUtil::getTheme());
    $renderer->assign('modname', $params['modname']);
    $renderer->assign('type', $params['type']);
    $renderer->assign('title', $params['title']);
    $renderer->assign('titlelink', $titlelink);
    $renderer->assign('truncated', mb_strlen($params['title']) > $cutlenght);
    $renderer->assign('titletruncated', mb_substr($params['title'], 0, $cutlenght) . '...');
    $renderer->assign('setpagetitle', $setpagetitle);
    $renderer->assign('insertstatusmsg', $insertstatusmsg);
    $renderer->assign('menufirst', $menufirst);
    $renderer->assign('image', $image);

    if ($assign) {
        $view->assign($assign, $renderer->fetch('moduleheader.tpl'));
    } else {
        return $renderer->fetch('moduleheader.tpl');
    }
}
