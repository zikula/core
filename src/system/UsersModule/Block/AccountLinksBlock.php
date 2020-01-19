<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;

class AccountLinksBlock extends AbstractBlockHandler
{
    /**
     * @var ExtensionMenuCollector
     */
    private $extensionMenuCollector;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('Accountlinks::', $properties['title'] . '::', ACCESS_READ)) {
            return '';
        }

        $extensionMenus = $this->extensionMenuCollector->getAllByType(ExtensionMenuInterface::TYPE_ACCOUNT);
        if (empty($accountLinks)) {
            return '';
        }

        return $this->renderView('@ZikulaUsersModule/Block/accountLinks.html.twig', [
            'extensionMenus' => $extensionMenus
        ]);
    }

    /**
     * @required
     */
    public function setExtensionModuleCollector(ExtensionMenuCollector $extensionMenuCollector): void
    {
        $this->extensionMenuCollector = $extensionMenuCollector;
    }
}
