<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

class Blocks_Admin extends Zikula_Controller
{
    /**
     * the main administration function
     *
     * view() function)
     * @author Jim McDonald
     * @return string HTML output string
     */
    public function main()
    {
        // Security check will be done in view()
        return $this->view();
    }

    /**
     * View all blocks
     * @author Jim McDonald
     * @return string HTML output string
     */
    public function view()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $sFilter = SessionUtil::getVar('filter', array(), '/Blocks');
        $filter  = FormUtil::getPassedValue('filter', $sFilter);
        $clear   = FormUtil::getPassedValue('clear', 0);
        if ($clear) {
            $filter  = array();
            SessionUtil::setVar('filter', $filter, '/Blocks');
        } else {
            if (isset($filter['sort']) && $filter['sort']) {
                $oldSort    = SessionUtil::getVar('sort', '', '/Blocks/filter');
                $oldSortDir = SessionUtil::getVar('sortdir', '', '/Blocks/filter');
                if ($oldSort == $filter['sort']) {
                    if ($oldSortDir == 'ASC' || !$oldSortDir) {
                        $filter['sortdir'] = 'DESC';
                    } elseif ($oldSortDir == 'DESC') {
                        $filter['sortdir'] = 'ASC';
                    }
                }
            }
            SessionUtil::setVar('filter', $filter, '/Blocks');
        }

        // Create output object
        $pnRender = Renderer::getInstance('Blocks', false);

        // generate an authorisation key for the links
        $authid = SecurityUtil::generateAuthKey();

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
        foreach($blockspositions as $blocksposition) {
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
            foreach($thisblockspositions as $singleblockposition){
                $bposarray[] = $allbposarray[$singleblockposition['pid']];
            }
            $block['positions'] = implode(', ', $bposarray);
            unset($bposarray);

            // calculate what options the user has over this block
            $block['options'] = array();
            if ($block['active']) {
                $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'deactivate',
                        array('bid' => $block['bid'], 'authid' => $authid)),
                        'image' => 'folder_grey.gif',
                        'title' => $this->__('Deactivate'),
                        'noscript' => true);
            } else {
                $block['options'][] = array ('url' => ModUtil::url('Blocks', 'admin', 'activate',
                        array('bid' => $block['bid'], 'authid' => $authid)),
                        'image' => 'folder_green.gif',
                        'title' => $this->__('Activate'),
                        'noscript' => true);
            }

            $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $block['bid'])),
                    'image' => 'xedit.gif',
                    'title' => $this->__('Edit'),
                    'noscript' => false);
            $block['options'][] = array('url' => ModUtil::url('Blocks', 'admin', 'delete', array('bid' => $block['bid'])),
                    'image' => '14_layer_deletelayer.gif',
                    'title' => $this->__('Delete'),
                    'noscript' => false);

            $blocksitems[] = $block;

        }
        $pnRender->assign('blocks', $blocksitems);

        // get the block positions
        $items = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');

        // Loop through each returned item adding in the options that the user has over the item
        foreach ($items as $key => $item) {
            if (SecurityUtil::checkPermission('Blocks::', "$item[name]::", ACCESS_READ)) {
                $options = array();
                if (SecurityUtil::checkPermission('Blocks::', "$item[name]::$", ACCESS_EDIT)) {
                    $options[] = array('url'   => ModUtil::url('Blocks', 'admin', 'modifyposition', array('pid' => $item['pid'])),
                            'image' => 'xedit.gif',
                            'title' => $this->__('Edit'));
                    if (SecurityUtil::checkPermission('Blocks::', "$item[name]::", ACCESS_DELETE)) {
                        $options[] = array('url'   => ModUtil::url('Blocks', 'admin', 'deleteposition', array('pid' => $item['pid'])),
                                'image' => '14_layer_deletelayer.gif',
                                'title' => $this->__('Delete'));
                    }
                }
                // Add the calculated menu options to the item array
                $items[$key]['options'] = $options;
            }
        }

        // Assign the items to the template
        ksort($items);
        $pnRender->assign('positions', $items);
        $pnRender->assign('filter', $filter);

        // Return the output that has been generated by this function
        return $pnRender->fetch('blocks_admin_view.htm');
    }

    /**
     * deactivate a block
     * @author Jim McDonald
     * @param int $bid block id
     * @return string HTML output string
     */
    public function deactivate()
    {
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block now inactive.'));
        }

        // Redirect
        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * activate a block
     * @author Jim McDonald
     * @param int $bid block id
     * @return string HTML output string
     */
    public function activate()
    {
        // Get parameters
        $bid = FormUtil::getPassedValue('bid');

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'activate', array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Block now active.'));
        }

        // Redirect
        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * modify a block
     * @author Jim McDonald
     * @param int $bid block ind
     * @return string HTML output string
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
        $where = "WHERE pn_bid = '" . DataUtil::formatForStore($bid) . "'";
        $placements = DBUtil::selectObjectArray('block_placements', $where, 'pn_order', -1, -1, '', null);
        $blockinfo['placements']  = array();
        foreach ($placements as $placement) {
            $blockinfo['placements'][] = $placement['pid'];
        }

        // Load block
        $modinfo = ModUtil::getInfo($blockinfo['mid']);
        $blockObj = BlockUtil::load($modinfo['name'], $blockinfo['bkey']);
        if (!$blockObj) {
            return LogUtil::registerError($this->__('Sorry! No such block found.'), 404);
        }

        // Create output object
        $pnRender = Renderer::getInstance('Blocks', false);
        $pnRender->add_core_data();

        // Title - putting a title ad the head of each page reminds the user what
        // they are doing
        if (!empty($modinfo['name'])) {
            $pnRender->assign('modtitle', "$modinfo[name]/$blockinfo[bkey]");
        }

        // Add hidden block id to form
        $pnRender->assign('bid', $bid);

        // check for a valid set of filtering rules
        if (!isset($blockinfo['filter']) || empty($blockinfo['filter'])) {
            $blockinfo['filter']['modules'] = array();
            $blockinfo['filter']['type'] = '';
            $blockinfo['filter']['functions'] = '';
            $blockinfo['filter']['customargs'] = '';
        }

        // invert the filter array so that the output is in a useful form for the template
        if (isset($blockinfo['filter']['modules']) && is_array($blockinfo['filter']['modules'])) {
            $blockinfo['filter']['modules'] = array_flip($blockinfo['filter']['modules']);
        }

        // assign the block
        $pnRender->assign($blockinfo);

        // assign the list of modules
        $pnRender->assign('mods', ModUtil::getAllMods());

        // assign block positions
        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        $block_positions = array();
        foreach ($positions as $position) {
            $block_positions[$position['pid']] = $position['name'];
        }
        $pnRender->assign('block_positions', $block_positions);

        // Block-specific
        $blockoutput = '';
        if ($blockObj instanceof AbstractBlock) {
            $blockoutput = call_user_func(array($blockObj, 'modify'), $blockinfo);
        } else {
            $usname = preg_replace('/ /', '_', $modinfo['name']);
            $updatefunc = $usname . '_' . $blockinfo['bkey'] . 'block_modify';
            if (function_exists($updatefunc)) {
                $blockoutput = $updatefunc($blockinfo);
            }
        }

        // the blocks will have reset the renderDomain property (bad singleton design) - drak
        //889$pnRender->renderDomain = null;

        // Block output
        $pnRender->assign('blockoutput', $blockoutput);

        // Tableless for blockoutput
        if (!isset($GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['admin_tableless'])) {
            $GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['admin_tableless'] = false;
        }

        // Requirement for the block
        if (!isset($GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['requirement'])) {
            $GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]['requirement'] = '';
        }

        // Assign blockinfo to the template
        $pnRender->assign($GLOBALS['blocks_modules'][$blockinfo['mid']][$blockinfo['bkey']]);

        // Refresh
        $refreshtimes = array(   60 => $this->__('One minute'),
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
        $pnRender->assign('blockrefreshtimes' , $refreshtimes);

        // Return the output that has been generated by this function
        return $pnRender->fetch('blocks_admin_modify.htm');
    }

    /**
     * update a block
     * @author Jim McDonald
     * @see blocks_admin_modify()
     * @param int $bid block id to update
     * @param string $title the new title of the block
     * @param array $positions the new position(s) of the block
     * @param array $modules the modules to display the block on
     * @param string $url the new URL of the block
     * @param string $language the new language of the block
     * @param string $content the new content of the block
     * @return bool true if succesful, false otherwise
     */
    public function update()
    {
        // Get parameters
        $bid           = FormUtil::getPassedValue('bid');
        $title         = FormUtil::getPassedValue('title');
        $language      = FormUtil::getPassedValue('language');
        $collapsable   = FormUtil::getPassedValue('collapsable', 0);
        $defaultstate  = FormUtil::getPassedValue('defaultstate', 1);
        $content       = FormUtil::getPassedValue('content');
        $refresh       = FormUtil::getPassedValue('refresh');
        $positions     = FormUtil::getPassedValue('positions');
        $filter        = FormUtil::getPassedValue('filter', array());
        $returntoblock = FormUtil::getPassedValue('returntoblock');
        // not stored in a block
        $redirect      = FormUtil::getPassedValue('redirect', null);
        $cancel        = FormUtil::getPassedValue('cancel', null);
        if (isset($cancel)) {
            if (isset($redirect) && !empty($redirect)) {
                return System::redirect(urldecode($redirect));
            }
            return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
        }


        // Fix for null language
        if (!isset($language)) {
            $language = '';
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        // Get and update block info
        $blockinfo = BlockUtil::getBlockInfo($bid);
        $blockinfo['title'] = $title;
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
        if ($blockObj instanceof AbstractBlock) {
            $blockinfo = call_user_func(array($blockObj, 'update'), $blockinfo);
        } else {
            $usname = preg_replace('/ /', '_', $modinfo['name']);
            $updatefunc = $usname . '_' . $blockinfo['bkey'] . 'block_update';
            if (function_exists($updatefunc)) {
                $blockinfo = $updatefunc($blockinfo);
            }
        }

        if (!$blockinfo) {
            return System::redirect(ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $bid)));
        }

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'update', $blockinfo)) {
            // Success
            LogUtil::registerStatus($this->__('Done! Saved blocks.'));
        }

        if (isset($redirect) && !empty($redirect)) {
            return System::redirect(urldecode($redirect));
        }

        if (!empty($returntoblock)) {
            // load the block config again
            return System::redirect(ModUtil::url('Blocks', 'admin', 'modify',
                    array('bid' => $returntoblock)));
        }
        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * display form for a new block
     * @author Jim McDonald
     * @return string HTML output string
     */
    public function newblock()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $pnRender = Renderer::getInstance('Blocks', false);
        $pnRender->add_core_data();

        // Block
        // Load all blocks (trickier than it sounds)
        $blocks = BlockUtil::loadAll();
        if (!$blocks) {
            return LogUtil::registerError($this->__('Error! Could not load blocks.'));
        }

        $blockinfo = array();
        foreach ($blocks as $moduleblocks) {
            foreach ($moduleblocks as $block) {
                $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($block['module']));
                $blockinfo[$block['mid'] . ':' . $block['bkey']] =   $modinfo['displayname'] . '/' . $block['text_type_long'];
            }
        }
        $pnRender->assign('blockids', $blockinfo);

        // assign block positions
        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        $block_positions = array();
        foreach ($positions as $position) {
            $block_positions[$position['pid']] = $position['name'];
        }
        $pnRender->assign('block_positions', $block_positions);

        // Return the output that has been generated by this function
        return $pnRender->fetch('blocks_admin_newblock.htm');
    }

    /**
     * create a new block
     * @author Jim McDonald
     * @see blocks_admin_new()
     * @param string $title the new title of the block
     * @param int $blockid block id to create
     * @param string $language the language to assign to the block
     * @param string $position the position of the block
     * @return bool true if successful, false otherwise
     */
    public function create()
    {
        // Get parameters
        $title        = FormUtil::getPassedValue('title');
        $blockid      = FormUtil::getPassedValue('blockid');
        $language     = FormUtil::getPassedValue('language');
        $collapsable   = FormUtil::getPassedValue('collapsable', 0);
        $defaultstate  = FormUtil::getPassedValue('defaultstate', 1);
        $positions     = FormUtil::getPassedValue('positions');

        list($mid, $bkey) = explode(':', $blockid);

        // Fix for null language
        if (!isset($language)) {
            $language = '';
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        $blockinfo = array('bkey'         => $bkey,
                'title'        => $title,
                'positions'    => $positions,
                'mid'          => $mid,
                'language'     => $language,
                'collapsable'  => $collapsable,
                'defaultstate' => $defaultstate);

        // Pass to API
        $bid = ModUtil::apiFunc('Blocks', 'admin', 'create', $blockinfo);
        if ($bid != false) {
            LogUtil::registerStatus($this->__('Done! Created block.'));
            return System::redirect(ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $bid)));
        }

        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * delete a block
     * @author Jim McDonald
     * @param int bid the block id
     * @param bool confirm to delete block
     * @return string HTML output string
     */
    public function delete()
    {
        // Get parameters
        $bid          = FormUtil::getPassedValue('bid');
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
            // Create output object
            $pnRender = Renderer::getInstance('Blocks', false);

            // get the module info
            $modinfo = ModUtil::getInfo($blockinfo['mid']);

            if (!empty($modinfo['name'])) {
                $pnRender->assign('blockname', "$modinfo[name]/$blockinfo[bkey]");
            } else {
                $pnRender->assign('blockname', "Core/$blockinfo[bkey]");
            }

            // add the block id
            $pnRender->assign('bid', $bid);

            // Return the output that has been generated by this function
            return $pnRender->fetch('blocks_admin_delete.htm');
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        // Pass to API
        if (ModUtil::apiFunc('Blocks', 'admin', 'delete',
        array('bid' => $bid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted block.'));
        }

        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * display a form to create a new block position
     *
     * @author Mark West
     */
    public function newposition()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $pnRender = Renderer::getInstance('Blocks', false);

        // Return the output that has been generated by this function
        return $pnRender->fetch('blocks_admin_newposition.htm');
    }

    /**
     * display a form to create a new block position
     *
     * @author Mark West
     */
    public function createposition()
    {
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

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        // add the new block position
        if (ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => $position['name'], 'description' => $position['description']))) {
            LogUtil::registerStatus($this->__('Done! Created block.'));
        }

        // all done
        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * display a form to create a new block position
     *
     * @author Mark West
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

        // Create output object
        $pnRender = Renderer::getInstance('Blocks', false);

        // assign the item
        $pnRender->assign($position);

        // get all blocks in the position
        $block_placements = ModUtil::apiFunc('blocks', 'user', 'getblocksinposition', array('pid' => $pid));

        // get all defined blocks
        $allblocks = ModUtil::apiFunc('Blocks', 'user', 'getall', array('active_status' => 0));
        foreach($allblocks as $key => $allblock) {
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
            foreach($allblocks as $key => $allblock) {
                if ($allblock['bid'] == $blockplacement['bid']) {
                    unset($allblocks[$key]);
                    $block['modname'] = $allblock['modname'];
                }
            }
            $blocks[] = $block;
        }

        $pnRender->assign('assignedblocks', $blocks);
        $pnRender->assign('unassignedblocks', $allblocks);

        // Return the output that has been generated by this function
        return $pnRender->fetch('blocks_admin_modifyposition.htm');
    }

    /**
     * display a form to create a new block position
     *
     * @author Mark West
     */
    public function updateposition()
    {
        // Get parameters
        $position = FormUtil::getPassedValue('position');

        // check our vars
        if (!isset($position['pid']) || !isset($position['name']) || !isset($position['description'])) {
            return LogUtil::registerArgsError(ModUtil::url('Blocks', 'admin', 'view'));
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        // update the position
        if (ModUtil::apiFunc('Blocks', 'admin', 'updateposition',
        array('pid' => $position['pid'], 'name' => $position['name'], 'description' => $position['description']))) {
            // all done
            LogUtil::registerStatus($this->__('Done! Saved block.'));
        }

        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * delete a block position
     *
     * @author Mark West
     * @param int $args['pid'] the id of the position to be deleted
     * @param int $args['objectid'] generic object id maps to pid if present
     * @param bool $args['confirmation'] confirmation that this item can be deleted
     * @return mixed HTML string if confirmation is null, true if delete successful, false otherwise
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
            $pnRender = Renderer::getInstance('Blocks', false);
            $pnRender->assign('pid', $pid);
            return $pnRender->fetch('blocks_admin_deleteposition.htm');
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','view'));
        }

        if (ModUtil::apiFunc('Blocks', 'admin', 'deleteposition', array('pid' => $pid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted block position.'));
        }

        return System::redirect(ModUtil::url('Blocks', 'admin', 'view'));
    }

    /**
     * Any config options would likely go here in the future
     * @author Jim McDonald
     * @return string HTML output string
     */
    public function modifyconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $pnRender = Renderer::getInstance('Blocks', false);

        // assign all the module vars
        $pnRender->assign(ModUtil::getVar('Blocks'));

        // Return the output that has been generated by this function
        return $pnRender->fetch('blocks_admin_modifyconfig.htm');
    }

    /**
     * Set config variable(s)
     * @author Jim McDonald
     * @return string bool true if successful, false otherwise
     */
    public function updateconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $collapseable = FormUtil::getPassedValue('collapseable');

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Blocks','admin','main'));
        }

        if (!isset($collapseable) || !is_numeric($collapseable)) {
            $collapseable = 0;
        }

        ModUtil::setVar('Blocks', 'collapseable', $collapseable);

        // Let any other modules know that the modules configuration has been updated
        ModUtil::callHooks('module','updateconfig','Blocks', array('module' => 'Blocks'));

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        return System::redirect(ModUtil::url('Blocks', 'admin', 'main'));
    }
}