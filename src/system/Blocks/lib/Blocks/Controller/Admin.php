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

/**
 * Blocks_Controller_Admin class.
 */
class Blocks_Controller_Admin extends Zikula_AbstractController
{

    /**
     * The main administration function.
     *
     * @return string HTML output string.
     */
    public function main()
    {
        // Security check will be done in view()
		$this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * View all blocks.
     *
     * @return string HTML output string.
     */
    public function view()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        $sfilter = SessionUtil::getVar('filter', array(), '/Blocks');
        $filter = FormUtil::getPassedValue('filter', $sfilter);
        $clear = FormUtil::getPassedValue('clear', 0);
        if ($clear) {
            $filter = array();
            SessionUtil::setVar('filter', $filter, '/Blocks');
        }

        // sort and sortdir GET parameters override filter values
        $sort = (isset($filter['sort']) && !empty($filter['sort'])) ? strtolower($filter['sort']) : 'bid';
        $sortdir = (isset($filter['sortdir']) && !empty($filter['sortdir'])) ? strtoupper($filter['sortdir']) : 'ASC';
        $filter['sort'] = FormUtil::getPassedValue('sort', $sort, 'GET');
        $filter['sortdir'] = FormUtil::getPassedValue('sortdir', $sortdir, 'GET');
        if ($filter['sortdir'] != 'ASC' && $filter['sortdir'] != 'DESC') {
                $filter['sortdir'] = 'ASC';
        }
        $filter['blockposition_id'] = isset($filter['blockposition_id']) ? $filter['blockposition_id'] : 0;
        $filter['modid'] = isset($filter['modid']) ? $filter['modid'] : 0;
        $filter['language'] = isset($filter['language']) ? $filter['language'] : '';
        $filter['active_status'] = isset($filter['active_status']) ? $filter['active_status'] : 0;

        // generate an authorisation key for the links
        $token = SecurityUtil::generateCsrfToken($this->serviceManager, true);

        // set some default variables
        $rownum = 1;
        $lastpos = '';

        // Get all blocks
        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall', $filter);

        // we can easily count the number of blocks using count() rather than
        // calling the api function
        $numrows = count($blocks);

        // create an empty arrow to hold the processed items
        $blockitems = array();

        // get all possible block positions
        $blockspositions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        // build assoc array for easier usage later on
        foreach ($blockspositions as $blocksposition) {
            $allbposarray[$blocksposition['pid']] = $blocksposition['name'];
        }
        // loop round each item calculating the additional information
        $blocksitems = array();
        foreach ($blocks as $key => $block) {

            // set the module that holds the block
            $modinfo = ModUtil::getInfo($block['mid']);
            $block['modname'] = $modinfo['displayname'];

            // set the blocks language
            if (empty($block['language'])) {
                $block['language'] = $this->__('All');
            } else {
                $block['language'] = ZLanguage::getLanguageName($block['language']);
            }

            $thisblockspositions = ModUtil::apiFunc('Blocks', 'user', 'getallblockspositions', array('bid' => $block['bid']));
            $bposarray = array();
            foreach ($thisblockspositions as $singleblockposition) {
                $bposarray[] = $allbposarray[$singleblockposition['pid']];
            }
            $block['positions'] = implode(', ', $bposarray);
            unset($bposarray);

            // calculate what options the user has over this block
            $block['options'] = array();
            if ($block['active']) {
                $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'deactivate',
                                array('bid' => $block['bid'], 'csrftoken' => $token)),
                        'image' => 'folder_grey.png',
                        'title' => $this->__f('Deactivate \'%s\'', $block['title']),
                        'noscript' => true);
            } else {
                $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'activate',
                                array('bid' => $block['bid'], 'csrftoken' => $token)),
                        'image' => 'folder_green.png',
                        'title' => $this->__f('Activate \'%s\'', $block['title']),
                        'noscript' => true);
            }

            $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $block['bid'])),
                    'image' => 'xedit.png',
                    'title' => $this->__f('Edit \'%s\'', $block['title']),
                    'noscript' => false);
            $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'delete', array('bid' => $block['bid'])),
                    'image' => '14_layer_deletelayer.png',
                    'title' => $this->__f('Delete \'%s\'', $block['title']),
                    'noscript' => false);

            $blocksitems[] = $block;
        }
        $this->view->assign('blocks', $blocksitems);

        // get the block positions
        $items = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');

        // Loop through each returned item adding in the options that the user has over the item
        foreach ($items as $key => $item) {
            if (SecurityUtil::checkPermission('Blocks::', "$item[name]::", ACCESS_READ)) {
                $options = array();
                if (SecurityUtil::checkPermission('Blocks::', "$item[name]::$", ACCESS_EDIT)) {
                    $options[] = array('url' => ModUtil::url('Blocks', 'admin', 'modifyposition', array('pid' => $item['pid'])),
                            'image' => 'xedit.png',
                            'title' => $this->__f('Edit blockposition \'%s\'', $item['name']));
                    if (SecurityUtil::checkPermission('Blocks::', "$item[name]::", ACCESS_DELETE)) {
                        $options[] = array('url' => ModUtil::url('Blocks', 'admin', 'deleteposition', array('pid' => $item['pid'])),
                                'image' => '14_layer_deletelayer.png',
                                'title' => $this->__f('Delete blockposition \'%s\'', $item['name']));
                    }
                }
                // Add the calculated menu options to the item array
                $items[$key]['options'] = $options;
            }
        }

        // Assign the items to the template
        ksort($items);
        $this->view->assign('positions', $items);

        $this->view->assign('filter', $filter)
                ->assign('sort', $filter['sort'])
                ->assign('sortdir', $filter['sortdir']);

        // Return the output that has been generated by this function
        return $this->view->fetch('blocks_admin_view.tpl');
    }

    /**
     * Deactivate a block.
     *
     * @param int $bid block id
     *
     * @return string HTML output string.
     */
    public function deactivate()
    {
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block now inactive.'));
        }

        // Redirect
        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Activate a block.
     *
     * @param int $bid block id.
     *
     * @return string HTML output string.
     */
    public function activate()
    {
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'activate', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block now active.'));
        }

        // Redirect
        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Modify a block.
     *
     * @param int $bid block id.
     *
     * @return string HTML output string.
     */
    public function modify()
    {
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');

        // Get details on current block
        $blockinfo = BlockUtil::getBlockInfo($bid);

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$blockinfo[bid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // check the blockinfo array
        if (empty($blockinfo)) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // get the block placements
        $where = "WHERE z_bid = '" . DataUtil::formatForStore($bid) . "'";
        $placements = DBUtil::selectObjectArray('block_placements', $where, 'z_order', -1, -1, '', null);
        $blockinfo['placements'] = array();
        foreach ($placements as $placement) {
            $blockinfo['placements'][] = $placement['pid'];
        }

        // Load block
        $modinfo = ModUtil::getInfo($blockinfo['mid']);
        $blockObj = BlockUtil::load($modinfo['name'], $blockinfo['bkey']);
        if (!$blockObj) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        $this->view->setCaching(false);

        $this->view->add_core_data();

        // Title - putting a title ad the head of each page reminds the user what
        // they are doing
        if (!empty($modinfo['name'])) {
            $this->view->assign('modtitle', "$modinfo[name]/$blockinfo[bkey]");
        }

        // Add hidden block id to form
        $this->view->assign('bid', $bid);

        // assign the block
        $this->view->assign($blockinfo);

        // assign the list of modules
        $this->view->assign('mods', ModUtil::getAllMods());

        // assign block positions
        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        $block_positions = array();
        foreach ($positions as $position) {
            $block_positions[$position['pid']] = $position['name'];
        }
        $this->view->assign('block_positions', $block_positions);

        // Block-specific
        $blockoutput = '';
        if ($blockObj instanceof Zikula_Controller_AbstractBlock) {
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
        $refreshtimes = array(60 => $this->__('One minute'),
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
        return $this->view->fetch('blocks_admin_modify.tpl');
    }

    /**
     * Update a block.
     *
     * @param int $bid block id to update.
     * @param string $title the new title of the block.
     * @param string $description the new description of the block.
     * @param array $positions the new position(s) of the block.
     * @param array $modules the modules to display the block on.
     * @param string $url the new URL of the block.
     * @param string $language the new language of the block.
     * @param string $content the new content of the block.
     *
     * @see blocks_admin_modify()
     *
     * @return bool true if succesful, false otherwise
     */
    public function update()
    {
        $this->checkCsrfToken();
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');
        $title = FormUtil::getPassedValue('title');
        $description = FormUtil::getPassedValue('description');
        $language = FormUtil::getPassedValue('language');
        $collapsable = FormUtil::getPassedValue('collapsable', 0);
        $defaultstate = FormUtil::getPassedValue('defaultstate', 1);
        $content = FormUtil::getPassedValue('content');
        $refresh = FormUtil::getPassedValue('refresh');
        $positions = FormUtil::getPassedValue('positions');
        $filter = FormUtil::getPassedValue('filters', array());
        $returntoblock = FormUtil::getPassedValue('returntoblock');

        // not stored in a block
        $redirect = FormUtil::getPassedValue('redirect', null);
        $cancel = FormUtil::getPassedValue('cancel', null);

        if (isset($cancel)) {
            if (isset($redirect) && !empty($redirect)) {
                $this->redirect(urldecode($redirect));
            }
            $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
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
        if ($blockObj instanceof Zikula_Controller_AbstractBlock) {
            $blockinfo = call_user_func(array($blockObj, 'update'), $blockinfo);
        } else {
            $usname = preg_replace('/ /', '_', $modinfo['name']);
            $updatefunc = $usname . '_' . $blockinfo['bkey'] . 'block_update';
            if (function_exists($updatefunc)) {
                $blockinfo = $updatefunc($blockinfo);
            }
        }

        if (!$blockinfo) {
            $this->redirect(ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $bid)));
        }

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'update', $blockinfo)) {
            // Success
            LogUtil::registerStatus($this->__('Done! Saved blocks.'));
        }

        if (isset($redirect) && !empty($redirect)) {
            $this->redirect(urldecode($redirect));
        }

        if (!empty($returntoblock)) {
            // load the block config again
            $this->redirect(ModUtil::url('Blocks', 'admin', 'modify',
                            array('bid' => $returntoblock)));
        }
        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Display form for a new block.
     *
     * @return string HTML output string.
     */
    public function newblock()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        $this->view->add_core_data();

        // Block
        // Load all blocks (trickier than it sounds)
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
        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        $block_positions = array();
        foreach ($positions as $position) {
            $block_positions[$position['pid']] = $position['name'];
        }
        $this->view->assign('block_positions', $block_positions);

        // Return the output that has been generated by this function
        return $this->view->fetch('blocks_admin_newblock.tpl');
    }

    /**
     * Create a new block.
     *
     * @param string $title the new title of the block.
     * @param string $description the new description of the block.
     * @param int $blockid block id to create.
     * @param string $language the language to assign to the block.
     * @param string $position the position of the block.
     *
     * @see blocks_admin_new()
     *
     * @return bool true if successful, false otherwise.
     */
    public function create()
    {
        $this->checkCsrfToken();
        // Get parameters
        $title = FormUtil::getPassedValue('title');
        $description = FormUtil::getPassedValue('description');
        $blockid = FormUtil::getPassedValue('blockid');
        $language = FormUtil::getPassedValue('language');
        $collapsable = FormUtil::getPassedValue('collapsable', 0);
        $defaultstate = FormUtil::getPassedValue('defaultstate', 1);
        $positions = FormUtil::getPassedValue('positions');

        list($mid, $bkey) = explode(':', $blockid);

        // Fix for null language
        if (!isset($language)) {
            $language = '';
        }

        $blockinfo = array(
                'bkey' => $bkey,
                'title' => $title,
                'description' => $description,
                'positions' => $positions,
                'mid' => $mid,
                'language' => $language,
                'collapsable' => $collapsable,
                'defaultstate' => $defaultstate
        );

        // Pass to API
        $bid = ModUtil::apiFunc('Blocks', 'admin', 'create', $blockinfo);
        if ($bid != false) {
            LogUtil::registerStatus($this->__('Done! Created block.'));
            $this->redirect(ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $bid)));
        }

        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Delete a block.
     *
     * @param int bid the block id.
     * @param bool confirm to delete block.
     *
     * @return string HTML output string.
     */
    public function delete()
    {
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');
        $confirmation = FormUtil::getPassedValue('confirmation');

        // Get details on current block
        $blockinfo = BlockUtil::getBlockInfo($bid);

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$blockinfo[bid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        if ($blockinfo == false) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // Check for confirmation
        if (empty($confirmation)) {
            // No confirmation yet - get one
            $this->view->setCaching(false);

            // get the module info
            $modinfo = ModUtil::getInfo($blockinfo['mid']);

            if (!empty($modinfo['name'])) {
                $this->view->assign('blockname', "$modinfo[name]/$blockinfo[bkey]");
            } else {
                $this->view->assign('blockname', "Core/$blockinfo[bkey]");
            }

            // add the block id
            $this->view->assign('bid', $bid);

            // Return the output that has been generated by this function
            return $this->view->fetch('blocks_admin_delete.tpl');
        }

        $this->checkCsrfToken();

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'delete',
                        array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted block.'));
        }

        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function newposition()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        // Return the output that has been generated by this function
        return $this->view->fetch('blocks_admin_newposition.tpl');
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function createposition()
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::position', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $position = FormUtil::getPassedValue('position');

        // check our vars
        if (!isset($position['name']) || !preg_match('/^[a-z0-9_-]*$/i', $position['name']) || !isset($position['description'])) {
            return LogUtil::registerArgsError(ModUtil::url('Blocks', 'admin', 'view'));
        }

        // add the new block position
        if (ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => $position['name'], 'description' => $position['description']))) {
            LogUtil::registerStatus($this->__('Done! Created block.'));
        }

        // all done
        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function modifyposition()
    {
        // get our input
        $pid = FormUtil::getPassedValue('pid');

        // get the block position
        $position = ModUtil::apiFunc('Blocks', 'user', 'getposition', array('pid' => $pid));

        // Security check
        if (!SecurityUtil::checkPermission("Blocks::$position[name]", '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        // assign the item
        $this->view->assign($position);

        // get all blocks in the position
        $block_placements = ModUtil::apiFunc('blocks', 'user', 'getblocksinposition', array('pid' => $pid));

        // get all defined blocks
        $allblocks = ModUtil::apiFunc('Blocks', 'user', 'getall', array('active_status' => 0));
        foreach ($allblocks as $key => $allblock) {
            // set the module that holds the block
            $modinfo = ModUtil::getInfo($allblock['mid']);
            $allblocks[$key]['modname'] = $modinfo['name'];
        }


        // loop over arrays forming a list of blocks not in the block positon and obtaining
        // full details on those that are
        $blocks = array();
        foreach ($block_placements as $blockplacement) {
            $block = BlockUtil::getBlockInfo($blockplacement['bid']);
            $block['order'] = $blockplacement['order'];
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
        return $this->view->fetch('blocks_admin_modifyposition.tpl');
    }

    /**
     * Display a form to create a new block position.
     *
     * @return string HTML output string.
     */
    public function updateposition()
    {
        $this->checkCsrfToken();

        // Get parameters
        $position = FormUtil::getPassedValue('position');

        // check our vars
        if (!isset($position['pid']) || !isset($position['name']) || !isset($position['description'])) {
            return LogUtil::registerArgsError(ModUtil::url('Blocks', 'admin', 'view'));
        }

        // update the position
        if (ModUtil::apiFunc('Blocks', 'admin', 'updateposition',
                        array('pid' => $position['pid'], 'name' => $position['name'], 'description' => $position['description']))) {
            // all done
            LogUtil::registerStatus($this->__('Done! Saved block.'));
        }

        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Delete a block position.
     *
     * @param int $args['pid'] the id of the position to be deleted.
     * @param int $args['objectid'] generic object id maps to pid if present.
     * @param bool $args['confirmation'] confirmation that this item can be deleted.
     *
     * @return mixed HTML string if confirmation is null, true if delete successful, false otherwise.
     */
    public function deleteposition($args)
    {
        $pid = FormUtil::getPassedValue('pid', isset($args['pid']) ? $args['pid'] : null, 'REQUEST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $pid = $objectid;
        }

        $item = ModUtil::apiFunc('Blocks', 'user', 'getposition', array('pid' => $pid));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such block position found.'), 404);
        }

        if (!SecurityUtil::checkPermission('Blocks::position', "$item[name]::$pid", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            $this->view->setCaching(false);

            $this->view->assign('pid', $pid);

            return $this->view->fetch('blocks_admin_deleteposition.tpl');
        }

        $this->checkCsrfToken();

        if (ModUtil::apiFunc('Blocks', 'admin', 'deleteposition', array('pid' => $pid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted block position.'));
        }

        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Any config options would likely go here in the future.
     *
     * @return string HTML output string.
     */
    public function modifyconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        // assign all the module vars
        $this->view->assign($this->getVars());

        // Return the output that has been generated by this function
        $this->view->fetch('blocks_admin_modifyconfig.tpl');
    }

    /**
     * Set config variable(s).
     *
     * @return string bool true if successful, false otherwise.
     */
    public function updateconfig()
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $collapseable = FormUtil::getPassedValue('collapseable');

        if (!isset($collapseable) || !is_numeric($collapseable)) {
            $collapseable = 0;
        }

        $this->setVar('collapseable', $collapseable);

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        $this->redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

}
