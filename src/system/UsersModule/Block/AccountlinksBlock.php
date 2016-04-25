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
use ModUtil;
use SecurityUtil;
use Zikula_View;

/**
 * A user-customizable block.
 */
class AccountlinksBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Accountlinks::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        return array(
            'module'         => $this->name,
            'text_type'      => $this->__('User account'),
            'text_type_long' => $this->__("User account links"),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true
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
     * @return string The rendered block.
     */
    public function display($blockInfo)
    {
        $renderedOutput = '';

        if (SecurityUtil::checkPermission('Accountlinks::', $blockInfo['title']."::", ACCESS_READ)) {
            // Get variables from content block
            $vars = BlockUtil::varsFromContent($blockInfo['content']);

            // Call the modules API to get the items
            if (ModUtil::available($this->name)) {
                $accountlinks = ModUtil::apiFunc($this->name, 'user', 'accountLinks');

                // Check for no items returned
                if (!empty($accountlinks)) {
                    $this->view->setCaching(Zikula_View::CACHE_DISABLED)
                               ->assign('accountlinks', $accountlinks);

                    // Populate block info and pass to theme
                    $blockInfo['content'] = $this->view->fetch('users_block_accountlinks.tpl');

                    $renderedOutput = BlockUtil::themeBlock($blockInfo);
                }
            }
        }

        return $renderedOutput;
    }
}
