<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 * @license http://www.gnu.org/copyleft/gpl.html
*/

/**
 * A user-customizable block.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Block_User extends AbstractBlock
{
    /**
     * initialise block
     *
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('Userblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return       array       The block information
     */
    public function info()
    {
        return array('module'         => 'Users',
                     'text_type'      => $this->__('User'),
                     'text_type_long' => $this->__("User's custom box"),
                     'allow_multiple' => false,
                     'form_content'   => false,
                     'form_refresh'   => false,
                     'show_preview'   => true);


    }

    /**
     * display block
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Userblock::', $blockinfo['title']."::", ACCESS_READ)) {
            return;
        }

        if (UserUtil::isLoggedIn() && UserUtil::getVar('ublockon') == 1) {
            if (!isset($blockinfo['title']) || empty($blockinfo['title'])) {
                $blockinfo['title'] = $this->__f('Custom block content for %s', UserUtil::getVar('name'));
            }
            $blockinfo['content'] = nl2br(UserUtil::getVar('ublock'));
            return BlockUtil::themeBlock($blockinfo);
        }
        return;
    }
}
