<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use BlockUtil;
use SecurityUtil;
use UserUtil;

/**
 * A user-customizable block.
 */
class UserBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Userblock::', 'Block title::');
    }

    /**
     * Get information on block.
     *
     * @return array The block information
     */
    public function info()
    {
        return array(
            'module'         => $this->name,
            'text_type'      => $this->__('User'),
            'text_type_long' => $this->__("User's custom box"),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true,
        );
    }

    /**
     * Display block.
     *
     * @param mixed[] $blockInfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string|void The rendered block if the user is logged in and the user block is enabled, void otherwise
     */
    public function display($blockInfo)
    {
        if (!SecurityUtil::checkPermission('Userblock::', $blockInfo['title']."::", ACCESS_READ)) {
            return;
        }

        if (UserUtil::isLoggedIn() && UserUtil::getVar('ublockon') == 1) {
            if (!isset($blockInfo['title']) || empty($blockInfo['title'])) {
                $blockInfo['title'] = $this->__f('Custom block content for %s', UserUtil::getVar('name'));
            }
            $blockInfo['content'] = nl2br(UserUtil::getVar('ublock'));

            return BlockUtil::themeBlock($blockInfo);
        }

        return;
    }
}
