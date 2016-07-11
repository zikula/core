<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;

class AccountLinksBlock extends AbstractBlockHandler
{
    /**
     * @param array $properties
     * @return string
     */
    public function display(array $properties)
    {
        $renderedOutput = '';

        if ($this->hasPermission('Accountlinks::', $properties['title'] . "::", ACCESS_READ)) {
            if (\ModUtil::available('ZikulaUsersModule')) {
                $accountLinks = $this->get('zikula_users_module.helper.account_links_helper')->getAllAccountLinks();
                if (!empty($accountLinks)) {
                    $renderedOutput = $this->renderView('@ZikulaUsersModule/Block/accountLinks.html.twig', [
                        'accountLinks' => $accountLinks
                    ]);
                }
            }
        }

        return $renderedOutput;
    }
}
