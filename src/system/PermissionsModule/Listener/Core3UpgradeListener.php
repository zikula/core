<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\Doctrine\ColumnExistsTrait;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;

class Core3UpgradeListener implements EventSubscriberInterface
{
    use ColumnExistsTrait;

    public static function getSubscribedEvents()
    {
        return [
            CoreUpgradePreExtensionUpgrade::class => 'upgrade'
        ];
    }

    public function upgrade(CoreUpgradePreExtensionUpgrade $event): void
    {
        if (!version_compare($event->getCurrentCoreVersion(), '3.0.0', '<')) {
            return;
        }
        if ($this->columnExists('group_perms', 'realm')) {
            $sql = 'ALTER TABLE `group_perms` DROP COLUMN `realm`;';
            $this->conn->executeQuery($sql);
        }
        if ($this->columnExists('group_perms', 'bond')) {
            $sql = 'ALTER TABLE `group_perms` DROP COLUMN `bond`;';
            $this->conn->executeQuery($sql);
        }
        if (!$this->columnExists('group_perms', 'comment')) {
            $sql = '
                ALTER TABLE `group_perms`
                ADD `comment` VARCHAR(255) NOT NULL AFTER `level`,
                ADD `colour` VARCHAR(10) NOT NULL AFTER `comment`;;
            ';
            $this->conn->executeQuery($sql);
        }
    }
}
