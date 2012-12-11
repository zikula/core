<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * A block that allows users to log into the system.
 *
 * @todo Add modify/update methods to allow the admin to show only certain methods on the
 *          block, to allow him to set the order that methods appear, and to allow him to
 *          set the blocktitle and method descriptions for different languages. See extmenu
 *          for an example.
 */
class Users_Block_Login extends Zikula_Controller_AbstractBlock
{

    /**
     * Post-construction initialization.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Disable caching by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * Initialise the block.
     *
     * Adds the blocks security schema to the PN environment.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Loginblock::', 'Block title::');
    }

    /**
     * Return the block info.
     *
     * @return array A blockinfo structure.
     */
    public function info()
    {
        return array(
            'module'         => $this->name,
            'text_type'      => $this->__('Log-in'),
            'text_type_long' => $this->__('Log-in block'),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => false,
        );
    }

    /**
     * Display the output of the login block.
     *
     * @param array $blockInfo A blockinfo structure.
     *
     * @return string The output.
     */
    public function display($blockInfo)
    {
        $renderedOutput = '';

        if (SecurityUtil::checkPermission('Loginblock::', $blockInfo['title'].'::', ACCESS_READ)) {
            if (!UserUtil::isLoggedIn()) {
                if (empty($blockInfo['title'])) {
                    $blockInfo['title'] = DataUtil::formatForDisplay('Login');
                }

                $authenticationMethodList = new Users_Helper_AuthenticationMethodList($this);

                if ($authenticationMethodList->countEnabledForAuthentication() > 1) {
                    $selectedAuthenticationMethod = $this->request->request->get('authentication_method', false);
                } else {
                    // There is only one (or there is none), so auto-select it.
                    $authenticationMethod = $authenticationMethodList->getAuthenticationMethodForDefault();
                    $selectedAuthenticationMethod = array(
                        'modname'   => $authenticationMethod->modname,
                        'method'    => $authenticationMethod->method,
                    );
                }

                // TODO - The order and availability should be set by block configuration
                $authenticationMethodDisplayOrder = array();
                foreach ($authenticationMethodList as $authenticationMethod) {
                    if ($authenticationMethod->isEnabledForAuthentication()) {
                        $authenticationMethodDisplayOrder[] = array(
                            'modname'   => $authenticationMethod->modname,
                            'method'    => $authenticationMethod->method,
                        );
                    }
                }

                $this->view->assign('authentication_method_display_order', $authenticationMethodDisplayOrder)
                           ->assign('selected_authentication_method', $selectedAuthenticationMethod);

                // If the current page was reached via a POST or FILES then we don't want to return here.
                // Only return if the current page was reached via a regular GET
                if ($this->request->isGet()) {
                    $this->view->assign('returnpage', System::getCurrentUri());
                } else {
                    $this->view->assign('returnpage', '');
                }

                $tplName = mb_strtolower("users_block_login_{$blockInfo['position']}.tpl");
                if ($this->view->template_exists($tplName)) {
                    $blockInfo['content'] = $this->view->fetch($tplName);
                } else {
                    $blockInfo['content'] = $this->view->fetch('users_block_login.tpl');
                }

                $renderedOutput = BlockUtil::themeBlock($blockInfo);
            }
        }

        return $renderedOutput;
    }

}
