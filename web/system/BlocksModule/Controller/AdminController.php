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

namespace BlocksModule\Controller;

use UserUtil, ModUtil, SecurityUtil, LogUtil, DataUtil, System, ZLanguage, CategoryRegistryUtil, CategoryUtil;
use PageUtil, ThemeUtil, BlockUtil, EventUtil, Zikula_View;

/**
 * Blocks_Controller_Admin class.
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * The main administration function.
     *
     * @return string HTML output string.
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * View all blocks.
     *
     * @return string HTML output string.
     */
    public function viewAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_EDIT)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $sfilter = $this->request->getSession()->get('blocks/filter', array());
        $filter = $this->request->get('filter', $sfilter);
        $clear = $this->request->get('clear', 0);
        if ($clear) {
            $filter = array();
            $this->request->getSession()->set('blocks/filter', $filter);
        }

        // sort and sortdir GET parameters override filter values
        $sort = (isset($filter['sort']) && !empty($filter['sort'])) ? strtolower($filter['sort']) : 'bid';
        $sortdir = (isset($filter['sortdir']) && !empty($filter['sortdir'])) ? strtoupper($filter['sortdir']) : 'ASC';
        $filter['sort'] = $this->request->query->get('sort', $sort);
        $filter['sortdir'] = $this->request->query->get('sortdir', $sortdir);
        if ($filter['sortdir'] != 'ASC' && $filter['sortdir'] != 'DESC') {
            $filter['sortdir'] = 'ASC';
        }
        $filter['blockposition_id'] = isset($filter['blockposition_id']) ? $filter['blockposition_id'] : 0;
        $filter['module_id'] = isset($filter['module_id']) ? $filter['module_id'] : 0;
        $filter['language'] = isset($filter['language']) ? $filter['language'] : '';
        $filter['active_status'] = isset($filter['active_status']) ? $filter['active_status'] : 0;

        $this->view->assign('filter', $filter)
                   ->assign('sort', $filter['sort'])
                   ->assign('sortdir', $filter['sortdir']);

        // generate an authorisation key for the links
        $csrftoken = SecurityUtil::generateCsrfToken($this->container, true);
        $this->view->assign('csrftoken', $csrftoken);

        // Get all blocks
        $blocks = ModUtil::apiFunc('BlocksModule', 'user', 'getall', $filter);

        // get all possible block positions and build assoc array for easier usage later on
        $blockspositions = ModUtil::apiFunc('BlocksModule', 'user', 'getallpositions');
        foreach ($blockspositions as $blocksposition) {
            $allbposarray[$blocksposition['pid']] = $blocksposition['name'];
        }

        // loop round each item calculating the additional information
        $blocksitems = array();
        foreach ($blocks as $key => $block) {

            $block = $block->toArray();

            // set the module that holds the block
            $modinfo = ModUtil::getInfo($block['mid']);
            $block['modname'] = $modinfo['displayname'];

            // set the block's language
            if (empty($block['language'])) {
                $block['language'] = $this->__('All');
            } else {
                $block['language'] = ZLanguage::getLanguageName($block['language']);
            }

            // set the block's position(s)
            $bposarray = array();
            $thisblockspositions = ModUtil::apiFunc('BlocksModule', 'user', 'getallblockspositions', array('bid' => $block['bid']));
            foreach ($thisblockspositions as $singleblockposition) {
                $bposarray[] = $allbposarray[$singleblockposition['pid']];
            }
            $block['positions'] = implode(', ', $bposarray);
            unset($bposarray);

            // push block to array
            $blocksitems[] = $block;
        }
        $this->view->assign('blocks', $blocksitems);

        // get the block positions and assign them to the template
        $positions = ModUtil::apiFunc('BlocksModule', 'user', 'getallpositions');
        $this->view->assign('positions', $positions);

        // Return the output that has been generated by this function
        return $this->view->fetch('Admin/view.tpl');
    }

    /**
     * Deactivate a block.
     *
     * @param int $bid block id
     *
     * @return string HTML output string.
     */
    public function deactivateAction()
    {
        // Get parameters
        $bid = $this->request->get('bid');
        $csrftoken = $this->request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Pass to API
        if (ModUtil::apiFunc('BlocksModule', 'admin', 'deactivate', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block now inactive.'));
        }

        // Redirect
        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Activate a block.
     *
     * @param int $bid block id.
     *
     * @return string HTML output string.
     */
    public function activateAction()
    {
        // Get parameters
        $bid = $this->request->get('bid');
        $csrftoken = $this->request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Pass to API
        if (ModUtil::apiFunc('BlocksModule', 'admin', 'activate', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block now active.'));
        }

        // Redirect
        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Modify a block.
     *
     * @param int $bid block id.
     *
     * @return string HTML output string.
     */
    public function modifyAction()
    {
        // Get parameters
        $bid = $this->request->get('bid');

        // Get details on current block
        $blockinfo = BlockUtil::getBlockInfo($bid);

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$blockinfo[bid]", ACCESS_EDIT)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // check the blockinfo array
        if (empty($blockinfo)) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // get the block's placements
        $placements = ModUtil::apiFunc('BlocksModule', 'user', 'getallblockspositions', array('bid' => $bid));
        $placements_pids = array();
        foreach ($placements as $placement) {
            $placements_pids[] = $placement['pid'];
        }
        $blockinfo['placements'] = $placements_pids;

        // Load block
        $modinfo = ModUtil::getInfo($blockinfo['mid']);
        $blockObj = BlockUtil::load($modinfo['name'], $blockinfo['bkey']);
        if (!$blockObj) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // Title - putting a title ad the head of each page reminds the user what
        // they are doing
        if (!empty($modinfo['name'])) {
            $this->view->assign('modtitle', "$modinfo[name]/$blockinfo[bkey]");
        }

        // Add hidden block id to form
        $this->view->assign('bid', $bid);

        // assign the block values to the template
        $this->view->assign($blockinfo);

        // build and assign the list of modules
        $homepage = array('_homepage_' => $this->__('Homepage'));
        $modules  = ModUtil::getAllMods();
        unset($modules['zikula']);
        foreach ($modules as $name => $module) {
            $modules[$name] = $module['displayname'];
        }
        asort($modules);

        $this->view->assign('mods', array_merge($homepage, $modules));

        // assign block positions
        $positions = ModUtil::apiFunc('BlocksModule', 'user', 'getallpositions');
        $block_positions = array();
        foreach ($positions as $position) {
            $block_positions[$position['pid']] = $position['name'];
        }
        $this->view->assign('block_positions', $block_positions);

        // Block-specific
        $blockoutput = '';
        if ($blockObj instanceof \Zikula_Controller_AbstractBlock) {
            $blockoutput = call_user_func(array($blockObj, 'modify'), $blockinfo);
        } else {
            $usname = preg_replace('/ /', '_', $modinfo['name']);
            $updatefunc = $usname . '_' . $blockinfo['bkey'] . 'block_modify';
            if (function_exists($updatefunc)) {
                $blockoutput = $updatefunc($blockinfo);
            }
        }

        // Block output
        $this->view->assign('blockoutput', $blockoutput);

        // Tableless for blockoutput
        if (!isset($GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['admin_tableless'])) {
            $GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['admin_tableless'] = false;
        }

        // Requirement for the block
        if (!isset($GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['requirement'])) {
            $GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['requirement'] = '';
        }

        // Assign blockinfo to the template
        $this->view->assign($GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]);

        // Refresh
        $refreshtimes = array(
            60 => $this->__('One minute'),
            120 => $this->__('Two minutes'),
            300 => $this->__('Five minutes'),
            600 => $this->__('Ten minutes'),
            900 => $this->__('Fifteen minutes'),
            1800 => $this->__('Half an hour'),
            3600 => $this->__('One hour'),
            7200 => $this->__('Two hours'),
            14400 => $this->__('Four hours'),
            43200 => $this->__('Twelve hours'),
            86400 => $this->__('One day'),
            172800 => $this->__('Two days'),
            259200 => $this->__('Three days'),
            345600 => $this->__('Four days'),
            432000 => $this->__('Five days'),
            518400 => $this->__('Six days'),
            604800 => $this->__('Seven days'));
        $this->view->assign('blockrefreshtimes', $refreshtimes);

        // Return the output that has been generated by this function
        return $this->view->fetch('Admin/modify.tpl');
    }

    /**
     * Update a block.
     *
     * @param int    $bid         block id to update.
     * @param string $title       the new title of the block.
     * @param string $description the new description of the block.
     * @param array  $positions   the new position(s) of the block.
     * @param array  $modules     the modules to display the block on.
     * @param string $url         the new URL of the block.
     * @param string $language    the new language of the block.
     * @param string $content     the new content of the block.
     *
     * @see blocks_admin_modify()
     *
     * @return bool true if succesful, false otherwise
     */
    public function updateAction()
    {
        $this->checkCsrfToken();

        // Get parameters
        $bid = $this->request->get('bid');
        $title = $this->request->get('title');
        $description = $this->request->get('description');
        $language = $this->request->get('language');
        $collapsable = $this->request->get('collapsable', 0);
        $defaultstate = $this->request->get('defaultstate', 1);
        $content = $this->request->get('content');
        $refresh = $this->request->get('refresh');
        $positions = $this->request->get('positions');
        $filter = $this->request->get('filters', array());
        $returntoblock = $this->request->get('returntoblock');

        // not stored in a block
        $redirect = $this->request->get('redirect', null);
        $cancel = $this->request->get('cancel', null);

        if (isset($cancel)) {
            if (isset($redirect) && !empty($redirect)) {
                return $this->redirect(urldecode($redirect));
            }
            return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
        }


        // Fix for null language
        if (!isset($language)) {
            $language = '';
        }

        // Get and update block info
        $blockinfo = BlockUtil::getBlockInfo($bid);


        $blockinfo['title'] = $title;
        $blockinfo['description'] = $description;
        $blockinfo['bid'] = $bid;
        $blockinfo['language'] = $language;
        $blockinfo['collapsable'] = $collapsable;
        $blockinfo['defaultstate'] = $defaultstate;
        $blockinfo['content'] = $content;
        $blockinfo['refresh'] = $refresh;
        $blockinfo['positions'] = $positions;
        $blockinfo['filter'] = $filter;

        // Load block
        $modinfo = ModUtil::getInfo($blockinfo['mid']);
        $blockObj = BlockUtil::load($modinfo['name'], $blockinfo['bkey']);
        if (!$blockObj) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // Do block-specific update
        if ($blockObj instanceof \Zikula_Controller_AbstractBlock) {
            $blockinfo = call_user_func(array($blockObj, 'update'), $blockinfo);
        } else {
            $usname = preg_replace('/ /', '_', $modinfo['name']);
            $updatefunc = $usname . '_' . $blockinfo['bkey'] . 'block_update';
            if (function_exists($updatefunc)) {
                $blockinfo = $updatefunc($blockinfo);
            }
        }

        if (!$blockinfo) {
            return $this->redirect(ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $bid)));
        }

        // Pass to API
        if (ModUtil::apiFunc('BlocksModule', 'admin', 'update', $blockinfo)) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block saved.'));
        }

        if (isset($redirect) && !empty($redirect)) {
            return $this->redirect(urldecode($redirect));
        }

        if (!empty($returntoblock)) {
            // load the block config again
            return $this->redirect(ModUtil::url('Blocks', 'admin', 'modify',
                            array('bid' => $returntoblock)));
        }

        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Display form for a new block.
     *
     * @return string HTML output string.
     */
    public function newblockAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADD)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // Get parameters if exists
        $default = array(
            'title' => '',
            'description' => '',
            'language' => ZLanguage::getLanguageCode(),
            'blockid' => null,
            'positions' => array(),
            'collapsable' => 0,
            'defaultstate' => 1
        );
        $inputblock = $this->request->get('block', $default);

        // Block
        // Load all blocks
        $blocks = BlockUtil::loadAll();
        if (!$blocks) {
            return LogUtil::registerError($this->__('Error! Could not load blocks.'));
        }

        $blockinfo = array();
        foreach ($blocks as $moduleblocks) {
            foreach ($moduleblocks as $block) {
                $modinfo = ModUtil::getInfoFromName($block['module']);
                $blockinfo[$block['mid'] . ':' . $block['bkey']] = $modinfo['displayname'] . '/' . $block['text_type_long'];
            }
        }
        $this->view->assign('blockids', $blockinfo);

        // assign block positions
        $positions = ModUtil::apiFunc('BlocksModule', 'user', 'getallpositions');
        $block_positions = array();
        foreach ($positions as $position) {
            $block_positions[$position['pid']] = $position['name'];
        }
        $this->view->assign('block_positions', $block_positions);

        // Return the output that has been generated by this function
        return $this->view->assign('block', $inputblock)
                          ->fetch('Admin/newblock.tpl');
    }

    /**
     * Create a new block.
     *
     * @param string $title       the new title of the block.
     * @param string $description the new description of the block.
     * @param int    $blockid     block id to create.
     * @param string $language    the language to assign to the block.
     * @param string $position    the position of the block.
     *
     * @see blocks_admin_new()
     *
     * @return bool true if successful, false otherwise.
     */
    public function createAction()
    {
        $this->checkCsrfToken();

        // Get parameters
        $block = $this->request->get('block');

        if ($block['blockid'] == '') {
            $block['blockid'] = 'error';
            $url = ModUtil::url('Blocks', 'admin', 'newblock', array('block' => $block));

            return LogUtil::registerError($this->__('You must choose a block.'), null, $url);
        }

        list($mid, $bkey) = explode(':', $block['blockid']);
        $block['mid']  = $mid;
        $block['bkey'] = $bkey;

        // Fix for null language
        if (!isset($block['language'])) {
            $block['language'] = '';
        }

        // Default values
        $block['collapsable']  = isset($block['collapsable']) ? $block['collapsable'] : 0;
        $block['defaultstate'] = isset($block['defaultstate']) ? $block['defaultstate'] : 1;

        // Pass to API
        $bid = ModUtil::apiFunc('BlocksModule', 'admin', 'create', $block);

        if ($bid != false) {
            LogUtil::registerStatus($this->__('Done! Block created.'));
            return $this->redirect(ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $bid)));
        }

        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Delete a block.
     *
     * @param int bid the block id.
     * @param bool confirm to delete block.
     *
     * @return string HTML output string.
     */
    public function deleteAction()
    {
        // Get parameters
        $bid = $this->request->get('bid');
        $confirmation = $this->request->get('confirmation');

        // Get details on current block
        $blockinfo = BlockUtil::getBlockInfo($bid);

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$blockinfo[bid]", ACCESS_DELETE)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        if ($blockinfo == false) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // Check for confirmation
        if (empty($confirmation)) {
            // No confirmation yet - get one
            // get the module info
            $modinfo = ModUtil::getInfo($blockinfo['mid']);

            if (!empty($modinfo['name'])) {
                $this->view->assign('blockname', "$modinfo[name]/$blockinfo[bkey]");
            } else {
                $this->view->assign('blockname', "Core/$blockinfo[bkey]");
            }

            // add the block id
            $this->view->assign('block', $blockinfo);

            // Return the output that has been generated by this function
            return $this->view->fetch('Admin/delete.tpl');
        }

        $this->checkCsrfToken();

        // Pass to API
        if (ModUtil::apiFunc('BlocksModule', 'admin', 'delete', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block deleted.'));
        }

        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function newpositionAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $name = $this->request->get('name', '');

        // Return the output that has been generated by this function
        return $this->view->assign('name', $name)
                          ->fetch('Admin/newposition.tpl');
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function createpositionAction()
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::position', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // Get parameters
        $position = $this->request->get('position');

        // check our vars
        if (!isset($position['name']) || !preg_match('/^[a-z0-9_-]*$/i', $position['name']) || !isset($position['description'])) {
            return LogUtil::registerArgsError(ModUtil::url('Blocks', 'admin', 'view'));
        }

        // add the new block position
        $pid = ModUtil::apiFunc('BlocksModule', 'admin', 'createposition', array('name' => $position['name'], 'description' => $position['description']));

        if ($pid) {
            LogUtil::registerStatus($this->__('Done! Block position created.'));
            $this->redirect(ModUtil::url('Blocks', 'admin', 'modifyposition', array('pid' => $pid), null, 'blockpositionform'));
        }

        // all done
        return $this->redirect(ModUtil::url('Blocks', 'admin', 'modifyposition', array('pid' => $pid), null, 'blockpositionform'));
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function modifypositionAction()
    {
        // get our input
        $pid = $this->request->get('pid');

        // get the block position
        $position = ModUtil::apiFunc('BlocksModule', 'user', 'getposition', array('pid' => $pid));

        // Security check
        if (!SecurityUtil::checkPermission("Blocks::$position[name]", '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // assign the position item
        $this->view->assign('pid', $position['pid'])
                   ->assign('name', $position['name'])
                   ->assign('description', $position['description']);

        // get all blocks in the position
        $block_placements = ModUtil::apiFunc('blocksModule', 'user', 'getblocksinposition', array('pid' => $pid));

        // get all defined blocks
        $allblocks = ModUtil::apiFunc('BlocksModule', 'user', 'getall', array('active_status' => 0));
        foreach ($allblocks as $key => $allblock) {
            $allblock = $allblock->toArray();
            // set the module that holds the block
            $modinfo = ModUtil::getInfo($allblock['mid']);
            $allblock['modname'] = $modinfo['name'];
            $allblocks[$key] = $allblock;
        }

        // loop over arrays forming a list of blocks not in the block positon and obtaining
        // full details on those that are
        $blocks = array();
        foreach ($block_placements as $blockplacement) {
            $block = BlockUtil::getBlockInfo($blockplacement['bid']);
            foreach ($allblocks as $key => $allblock) {
                if ($allblock['bid'] == $blockplacement['bid']) {
                    unset($allblocks[$key]);
                    $block['modname'] = $allblock['modname'];
                }
            }
            $blocks[] = $block;
        }

        $this->view->assign('assignedblocks', $blocks)
                   ->assign('unassignedblocks', $allblocks);

        // Return the output that has been generated by this function
        return $this->view->fetch('Admin/modifyposition.tpl');
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function updatepositionAction()
    {
        $this->checkCsrfToken();

        // Get parameters
        $position = $this->request->get('position');

        // check our vars
        if (!isset($position['pid']) || !isset($position['name']) || !isset($position['description'])) {
            return LogUtil::registerArgsError(ModUtil::url('Blocks', 'admin', 'view'));
        }

        // update the position
        if (ModUtil::apiFunc('BlocksModule', 'admin', 'updateposition',
                        array('pid' => $position['pid'], 'name' => $position['name'], 'description' => $position['description']))) {
            // all done
            LogUtil::registerStatus($this->__('Done! Block position saved.'));

            $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
        }

        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Delete a block position.
     *
     * @param int  $args['pid']          the id of the position to be deleted.
     * @param int  $args['objectid']     generic object id maps to pid if present.
     * @param bool $args['confirmation'] confirmation that this item can be deleted.
     *
     * @return mixed HTML string if confirmation is null, true if delete successful, false otherwise.
     */
    public function deletepositionAction(array $args = array())
    {
        $pid = $this->request->get('pid', isset($args['pid']) ? $args['pid'] : null);
        $objectid = $this->request->get('objectid', isset($args['objectid']) ? $args['objectid'] : null);
        $confirmation = $this->request->request->get('confirmation', null);
        if (!empty($objectid)) {
            $pid = $objectid;
        }

        $item = ModUtil::apiFunc('BlocksModule', 'user', 'getposition', array('pid' => $pid));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such block position found.'), 404);
        }

        if (!SecurityUtil::checkPermission('Blocks::position', "$item[name]::$pid", ACCESS_DELETE)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            $this->view->assign('position', $item);

            return $this->view->fetch('Admin/deleteposition.tpl');
        }

        $this->checkCsrfToken();

        if (ModUtil::apiFunc('BlocksModule', 'admin', 'deleteposition', array('pid' => $pid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block position deleted.'));
        }

        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Any config options would likely go here in the future.
     *
     * @return string HTML output string.
     */
    public function modifyconfigAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // assign all the module vars
        $this->view->assign($this->getVars());

        // Return the output that has been generated by this function
        return $this->view->fetch('Admin/modifyconfig.tpl');
    }

    /**
     * Set config variable(s).
     *
     * @return string bool true if successful, false otherwise.
     */
    public function updateconfigAction()
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $collapseable = $this->request->get('collapseable');

        if (!isset($collapseable) || !is_numeric($collapseable)) {
            $collapseable = 0;
        }

        $this->setVar('collapseable', $collapseable);

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        return $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }
}
