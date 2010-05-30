<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

class Users_Block_Login extends AbstractBlock
{
    /**
     * return the block info
    */
    public function info()
    {
        return array(
        'module'         => 'Users',
        'text_type'      => $this->__('Log-in'),
        'text_type_long' => $this->__('Log-in block'),
        'allow_multiple' => false,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => false
        );
    }

    /**
     * initialise the block
     *
     * Adds the blocks security schema to the PN environment
    */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Loginblock::', 'Block title::');
    }

    /**
     * Display the block
     *
     * Display the output of the login block
    */
    public function display($row)
    {
        if (!SecurityUtil::checkPermission('Loginblock::', $row['title'].'::', ACCESS_READ)) {
            return;
        }

        if (!UserUtil::isLoggedIn()) {
            // create the output object
            $pnr = Renderer::getInstance('Users');
            // we don't need a cached id since the content of this block will always
            // be the same
            // check out if the contents are cached.
            // If this is the case, we do not need to make DB queries.
            if ($pnr->is_cached('users_block_login.htm')) {
                $row['content'] = $pnr->fetch('users_block_login.htm');
                return BlockUtil::themeBlock($row);
            }

            if (empty($row['title'])) {
                $row['title'] = DataUtil::formatForDisplay('Login');
            }

            $pnr->assign('seclevel', System::getVar('seclevel'));
            $pnr->assign('allowregistration', ModUtil::getVar('Users', 'reg_allowreg'));
            $pnr->assign('returnurl', System::getCurrentUri());
            // determine whether to show the rememberme option
            $pnr->assign('rememberme', System::getVar('seclevel'));
            $row['content'] = $pnr->fetch('users_block_login.htm');
            return BlockUtil::themeBlock($row);
        }

        return;
    }
}
