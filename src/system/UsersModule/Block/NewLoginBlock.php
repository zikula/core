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

use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * A block that allows users to log into the system.
 *
 * @todo Add modify/update methods to allow the admin to show only certain methods on the
 *          block, to allow him to set the order that methods appear, and to allow him to
 *          set the blocktitle and method descriptions for different languages. See extmenu
 *          for an example.
 */
class LoginBlock extends AbstractBlockHandler
{
    public function display(array $properties)
    {
        $renderedOutput = '';

        if ($this->hasPermission('Loginblock::', $properties['title'].'::', ACCESS_READ)) {
            if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
                if (empty($blockInfo['title'])) {
                    $blockInfo['title'] = $this->__('Login');
                }

                $authenticationMethodList = $this->get('zikula_users_module.helper.authentication_method_list_helper');
                $authenticationMethodList->initialize();
                $request = $this->get('request_stack')->getCurrentRequest();

                if ($authenticationMethodList->countEnabledForAuthentication() > 1) {
                    $selectedAuthenticationMethod = $request->request->get('authentication_method', false);
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

                $templateArgs = [
                    'authentication_method_display_order' => $authenticationMethodDisplayOrder,
                    'selected_authenticaton_method' => $selectedAuthenticationMethod
                ];

                // If the current page was reached via a POST or FILES then we don't want to return here.
                // Only return if the current page was reached via a regular GET
                $templateArgs['returnpage'] = $request->isMethod('GET') ? $request->getUri() : '';

                $tplName = mb_strtolower("login_{$blockInfo['position']}.tpl");
                if ($this->view->template_exists('Block/' . $tplName)) {
                    $blockInfo['content'] = $this->view->fetch('Block/' . $tplName);
                } else {
                    $blockInfo['content'] = $this->view->fetch('Block/login.tpl');
                }

                $renderedOutput = BlockUtil::themeBlock($blockInfo);
            }
        }

        return $renderedOutput;
    }
}
